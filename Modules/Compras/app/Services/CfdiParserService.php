<?php

namespace Modules\Compras\Services;

use Illuminate\Support\Facades\File;

/**
 * Servicio para parsear comprobantes CFDI (SAT México).
 *
 * Tipos de comprobante soportados:
 *  - I  → Ingreso  (factura normal)
 *  - E  → Egreso   (nota de crédito / devolución) — montos se invierten al negativo
 *  - P  → Pago     (complemento de pago)
 *  - T  → Traslado
 *  - N  → Nómina
 *
 * Facturas canceladas: se detectan por ausencia de TimbreFiscalDigital
 * o por el campo 'cancelada' en el registro de BD.
 */
class CfdiParserService
{
    private const NS_CFDI = 'http://www.sat.gob.mx/cfd/4';
    private const NS_TFD  = 'http://www.sat.gob.mx/TimbreFiscalDigital';
    private const NS_PAGO = 'http://www.sat.gob.mx/Pagos20';
    private const ASSETS  = 'Modules/Compras/resources/assets/json/catalogoSAT';

    /** Tipos cuyo importe se trata como negativo (reducen el saldo) */
    private const TIPOS_NEGATIVOS = ['E'];

    /** Tipos que NO participan en sumaSubTotal / sumaTotal */
    private const TIPOS_SIN_SUMA = ['P', 'T', 'N'];

    private array $catMetodoPago;
    private array $catRegimenFiscal;
    private array $catUsoCfdi;
    private array $catTipoComprobante;

    public function __construct()
    {
        $this->catMetodoPago      = $this->leerJson('metodoPago.json');
        $this->catRegimenFiscal   = $this->leerJson('regimenFiscal.json');
        $this->catUsoCfdi         = $this->leerJson('usoCFDI.json');
        $this->catTipoComprobante = $this->leerJson('tipoComprobante.json');
    }

    // -------------------------------------------------------------------------
    // API pública
    // -------------------------------------------------------------------------

    /**
     * Parsea una colección de documentos y devuelve la estructura normalizada.
     *
     * @param  iterable  $documentos  Registros con al menos la clave $xmlKey.
     * @param  string    $xmlKey      Campo que contiene la ruta al XML.
     * @param  array     $uuidsRef    UUIDs de facturas originales (para cruzar complementos de pago).
     */
    public function leerComprobante(iterable $documentos, string $xmlKey, array $uuidsRef = []): array
    {
        $resultado = $this->estructuraVacia();

        foreach ($documentos as $index => $doc) {
            $doc = (array) $doc;

            if (empty($doc[$xmlKey])) {
                $this->agregarDocumentoSinXml($resultado, $doc);
                continue;
            }

            $rutaXML = storage_path("app/{$doc[$xmlKey]}");
            if (!file_exists($rutaXML)) {
                continue;
            }

            $xml = $this->cargarXml($rutaXML);
            $this->procesarComprobante($xml, $doc, $resultado, $index, $uuidsRef);
        }

        return $resultado;
    }

    // -------------------------------------------------------------------------
    // Procesamiento principal
    // -------------------------------------------------------------------------

    private function procesarComprobante(\SimpleXMLElement $xml, array $doc, array &$resultado, int $index, array $uuidsRef): void
    {
        $uuid      = $this->extraerUuid($xml, $resultado);
        $cancelado = $this->esCancelado($xml, $doc);

        $comprobante = $xml->xpath('//cfdi:Comprobante')[0] ?? null;
        if ($comprobante) {
            $this->agregarDatosComprobante($comprobante, $doc, $resultado, $uuid, $index, $cancelado);
        }

        // Los CFDIs cancelados no procesan complementos de pago ni impuestos
        if (!$cancelado) {
            $this->procesarComplementosPago($xml, $doc, $resultado, $uuidsRef);
            $this->procesarImpuestos($xml, $resultado);
        }

        $this->procesarEmisor($xml, $resultado);
        $this->procesarReceptor($xml, $resultado);
    }

    // -------------------------------------------------------------------------
    // UUID y cancelación
    // -------------------------------------------------------------------------

    private function extraerUuid(\SimpleXMLElement $xml, array &$resultado): ?string
    {
        $timbres = $xml->xpath('//cfdi:Complemento/tfd:TimbreFiscalDigital');

        if (empty($timbres)) {
            return null; // Sin timbre = no válido ante el SAT
        }

        $uuid = (string) $timbres[0]['UUID'];
        $resultado['UUIDs'][] = $uuid;
        return $uuid;
    }

    /**
     * Determina si el CFDI está cancelado.
     *
     * Criterios (en orden de prioridad):
     *  1. El registro en BD tiene el campo 'cancelada' = true.
     *  2. El XML incluye el atributo MotivoCancelacion en el nodo Comprobante.
     *  3. No existe TimbreFiscalDigital (no fue timbrado o fue cancelado antes).
     */
    private function esCancelado(\SimpleXMLElement $xml, array $doc): bool
    {
        if (!empty($doc['cancelada'])) {
            return true;
        }

        $comprobante = $xml->xpath('//cfdi:Comprobante')[0] ?? null;
        if ($comprobante && !empty((string) $comprobante['MotivoCancelacion'])) {
            return true;
        }

        return empty($xml->xpath('//cfdi:Complemento/tfd:TimbreFiscalDigital'));
    }

    // -------------------------------------------------------------------------
    // Datos del comprobante
    // -------------------------------------------------------------------------

    private function agregarDatosComprobante(
        \SimpleXMLElement $nodo,
        array $doc,
        array &$resultado,
        ?string $uuid,
        int $index,
        bool $cancelado
    ): void {
        $tipo     = (string) $nodo['TipoDeComprobante'];
        $subtotal = (float)  $nodo['SubTotal'];
        $total    = (float)  $nodo['Total'];
        $factor   = in_array($tipo, self::TIPOS_NEGATIVOS) ? -1 : 1;

        // Nota de crédito (E): extrae los UUIDs de los CFDIs que reduce o cancela
        $uuidsRelacionados = [];
        if ($tipo === 'E') {
            foreach ($nodo->xpath('cfdi:CfdiRelacionados/cfdi:CfdiRelacionado') ?? [] as $rel) {
                $uuidsRelacionados[] = (string) $rel['UUID'];
            }
        }

        $resultado['comprobantes'][] = [
            'idRuta'                 => $doc['id'] ?? null,
            'fecha'                  => (string) $nodo['Fecha'],
            'folio'                  => (string) $nodo['Folio'],
            'serie'                  => (string) $nodo['Serie'],
            'formaPago'              => (string) $nodo['FormaPago'],
            'subTotal'               => $subtotal * $factor,
            'tComprobante'           => $tipo,
            'tComprobanteDesc'       => strtoupper($this->catTipoComprobante[$tipo]['descripcion'] ?? ''),
            'moneda'                 => (string) $nodo['Moneda'],
            'total'                  => $total * $factor,
            'UUID'                   => $uuid,
            // --- campos nuevos ---
            'cancelado'              => $cancelado,
            'motivoCancelacion'      => (string) $nodo['MotivoCancelacion'] ?: null,
            'esNotaCredito'          => $tipo === 'E',
            'uuidsRelacionados'      => $uuidsRelacionados,
            // ---------------------
            'xml'                    => $doc['xml'] ?? null,
            'representacion_impresa' => $doc['representacion_impresa'] ?? null,
        ];

        // Cancelados y tipos informativos (P/T/N) no afectan los totales
        if (!$cancelado && !in_array($tipo, self::TIPOS_SIN_SUMA)) {
            $resultado['sumaSubTotal'] += $subtotal * $factor;
            $resultado['sumaTotal']    += $total    * $factor;
        }

        // Método de pago: solo del primer comprobante
        if ($index === 0) {
            $mp = (string) $nodo['MetodoPago'];
            $resultado['metodoPago'] = [
                'metodoPago'     => $mp,
                'metodoPagoDesc' => $this->catMetodoPago[$mp]['descripcion'] ?? null,
            ];
        }
    }

    // -------------------------------------------------------------------------
    // Complementos de pago
    // -------------------------------------------------------------------------

    private function procesarComplementosPago(\SimpleXMLElement $xml, array $doc, array &$resultado, array $uuidsRef): void
    {
        $complementos = $xml->xpath('//cfdi:Complemento/pago20:Pagos/pago20:Pago');
        if (empty($complementos)) {
            return;
        }

        foreach ($complementos as $complemento) {
            $complemento->registerXPathNamespace('pago20', self::NS_PAGO);

            $tipo       = (string) $complemento['TipoDeComprobante'];
            $fechaPago  = (string) $complemento['FechaPago'];
            $monedaPago = (string) $complemento['MonedaP'];
            $formaPagoP = (string) $complemento['FormaDePagoP'];

            foreach ($complemento->xpath('pago20:DoctoRelacionado') as $docRel) {
                $uuid = (string) $docRel['IdDocumento'] ?: (string) ($docRel['UUID'] ?? '');

                if (empty($uuidsRef) || !in_array($uuid, $uuidsRef)) {
                    continue;
                }

                $numParcialidad = (string) $docRel['NumParcialidad'];
                $resultado['comprobantes'][] = [
                    'idRuta'                 => $doc['id'] ?? null,
                    'fecha'                  => $fechaPago,
                    'folio'                  => (string) $docRel['Folio'] ?: '-',
                    'serie'                  => (string) $docRel['Serie'] ?: '-',
                    'subTotal'               => (float) $docRel['ImpPagado'],
                    'formaPago'              => $formaPagoP ?: '-',
                    'tComprobante'           => $tipo,
                    'tComprobanteDesc'       => strtoupper(
                        ($this->catTipoComprobante[$tipo]['descripcion'] ?? '') . '-parc ' . $numParcialidad
                    ),
                    'moneda'                 => $monedaPago ?: '-',
                    'total'                  => (float) $docRel['ImpSaldoAnt'],
                    'UUID'                   => $uuid,
                    'cancelado'              => false,
                    'motivoCancelacion'      => null,
                    'esNotaCredito'          => false,
                    'uuidsRelacionados'      => [],
                    'xml'                    => $doc['xml'] ?? null,
                    'representacion_impresa' => $doc['representacion_impresa'] ?? null,
                ];
            }
        }
    }

    // -------------------------------------------------------------------------
    // Impuestos, emisor, receptor
    // -------------------------------------------------------------------------

    private function procesarImpuestos(\SimpleXMLElement $xml, array &$resultado): void
    {
        foreach ($xml->xpath('//cfdi:Impuestos') ?? [] as $imp) {
            if (isset($imp['TotalImpuestosTrasladados'])) {
                $resultado['impuestos'][] = (float) $imp['TotalImpuestosTrasladados'];
            }
        }
    }

    private function procesarEmisor(\SimpleXMLElement $xml, array &$resultado): void
    {
        $emisor = $xml->xpath('//cfdi:Emisor')[0] ?? null;
        if (!$emisor) {
            return;
        }

        $rf = (string) $emisor['RegimenFiscal'];
        $resultado['emisor'] = [
            'rfc'               => (string) $emisor['Rfc'],
            'nombre'            => (string) $emisor['Nombre'],
            'regimenFiscal'     => $rf,
            'regimenFiscalDesc' => $this->catRegimenFiscal[$rf]['descripcion'] ?? null,
        ];
    }

    private function procesarReceptor(\SimpleXMLElement $xml, array &$resultado): void
    {
        $receptor = $xml->xpath('//cfdi:Receptor')[0] ?? null;
        if (!$receptor) {
            return;
        }

        $uso = (string) $receptor['UsoCFDI'];
        $resultado['receptor'] = [
            'rfc'                     => (string) $receptor['Rfc'],
            'nombre'                  => (string) $receptor['Nombre'],
            'usoCFDI'                 => $uso,
            'usoCFDIDesc'             => $this->catUsoCfdi[$uso]['descripcion'] ?? null,
            'domicilioFiscalReceptor' => (string) $receptor['DomicilioFiscalReceptor'],
        ];
    }

    // -------------------------------------------------------------------------
    // Documento sin XML (ej. comprobante de pago sin archivo XML)
    // -------------------------------------------------------------------------

    private function agregarDocumentoSinXml(array &$resultado, array $doc): void
    {
        if (($doc['tipo_documento'] ?? '') !== 'comprobante_pago') {
            return;
        }

        $resultado['comprobantes'][] = [
            'idRuta'                 => $doc['id'] ?? null,
            'fecha'                  => $doc['fecha'],
            'folio'                  => '-',
            'serie'                  => '-',
            'subTotal'               => 0,
            'formaPago'              => '-',
            'tComprobante'           => '-',
            'tComprobanteDesc'       => strtoupper(str_replace('_', ' ', $doc['tipo_documento'])),
            'moneda'                 => '-',
            'total'                  => 0,
            'UUID'                   => '-',
            'cancelado'              => false,
            'motivoCancelacion'      => null,
            'esNotaCredito'          => false,
            'uuidsRelacionados'      => [],
            'xml'                    => $doc['xml'] ?? null,
            'representacion_impresa' => $doc['representacion_impresa'] ?? null,
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function cargarXml(string $ruta): \SimpleXMLElement
    {
        $xml = new \SimpleXMLElement(file_get_contents($ruta));
        $xml->registerXPathNamespace('cfdi',   self::NS_CFDI);
        $xml->registerXPathNamespace('tfd',    self::NS_TFD);
        $xml->registerXPathNamespace('pago20', self::NS_PAGO);
        return $xml;
    }

    private function leerJson(string $archivo): array
    {
        return json_decode(File::get(base_path(self::ASSETS . "/{$archivo}")), true);
    }

    private function estructuraVacia(): array
    {
        return [
            'comprobantes' => [],
            'impuestos'    => [],
            'emisor'       => [],
            'receptor'     => [],
            'metodoPago'   => [],
            'sumaSubTotal' => 0,
            'sumaTotal'    => 0,
            'UUIDs'        => [],
        ];
    }
}