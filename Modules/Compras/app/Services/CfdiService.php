<?php

namespace Modules\Compras\Services;

use Illuminate\Http\UploadedFile;
use Exception;

class CfdiService
{
    /**
     * Parsear un archivo subido (UploadedFile)
     */
    public function parsear(UploadedFile $archivo): array
    {
        return $this->parsearContenido(file_get_contents($archivo->getRealPath()));
    }

    /**
     * Parsear un archivo almacenado en disco (ruta)
     */
    public function parsearDesdeRuta(string $ruta): array
    {
        if (!file_exists($ruta)) {
            throw new Exception("El archivo no existe en la ruta: {$ruta}");
        }

        return $this->parsearContenido(file_get_contents($ruta));
    }

    /**
     * Lógica común de parseo (reutilizada)
     */
    private function parsearContenido(string $contenido): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($contenido);

        if ($xml === false) {
            throw new Exception('El archivo XML no es válido o está malformado.');
        }

        $ns = $xml->getNamespaces(true);
        $cfdi = $xml->attributes();

        $total      = (float) $cfdi['Total'];
        $subtotal   = (float) $cfdi['SubTotal'];
        $version    = (string) $cfdi['Version'];
        $folio      = (string) ($cfdi['Folio'] ?? '');
        $serie      = (string) ($cfdi['Serie'] ?? '');
        $fecha      = (string) $cfdi['Fecha'];
        $moneda     = (string) $cfdi['Moneda'];
        $tipoCambio = (float)  ($cfdi['TipoCambio'] ?? 1);
        $tipoComp   = (string) $cfdi['TipoDeComprobante'];

        $emisorNode   = $xml->children($ns['cfdi'] ?? 'http://www.sat.gob.mx/cfd/4')->Emisor;
        $receptorNode = $xml->children($ns['cfdi'] ?? 'http://www.sat.gob.mx/cfd/4')->Receptor;

        $emisorRfc      = (string) ($emisorNode->attributes()['Rfc']  ?? '');
        $emisorNombre   = (string) ($emisorNode->attributes()['Nombre'] ?? '');
        $receptorRfc    = (string) ($receptorNode->attributes()['Rfc']  ?? '');
        $receptorNombre = (string) ($receptorNode->attributes()['Nombre'] ?? '');

        $totalImpuestosTrasladados = 0.0;
        $totalImpuestosRetenidos   = 0.0;

        if (isset($xml->children($ns['cfdi'] ?? '')->Impuestos)) {
            $impuestos = $xml->children($ns['cfdi'] ?? '')->Impuestos->attributes();
            $totalImpuestosTrasladados = (float) ($impuestos['TotalImpuestosTrasladados'] ?? 0);
            $totalImpuestosRetenidos   = (float) ($impuestos['TotalImpuestosRetenidos']   ?? 0);
        }

        return [
            'version'                      => $version,
            'serie'                        => $serie,
            'folio'                        => $folio,
            'fecha'                        => $fecha,
            'moneda'                       => $moneda,
            'tipo_cambio'                  => $tipoCambio,
            'tipo_comprobante'             => $tipoComp,
            'subtotal'                     => $subtotal,
            'total'                        => $total,
            'total_impuestos_trasladados'  => $totalImpuestosTrasladados,
            'total_impuestos_retenidos'    => $totalImpuestosRetenidos,
            'emisor_rfc'                   => $emisorRfc,
            'emisor_nombre'                => $emisorNombre,
            'receptor_rfc'                 => $receptorRfc,
            'receptor_nombre'              => $receptorNombre,
        ];
    }

    /**
     * Valida que el total declarado coincida con subtotal + impuestos - retenciones.
     */
    public function validarTotal(array $datos): bool
    {
        $calculado = round(
            $datos['subtotal']
            + $datos['total_impuestos_trasladados']
            - $datos['total_impuestos_retenidos'],
            2
        );

        return $calculado === round($datos['total'], 2);
    }

/**
 * Extrae los conceptos (partidas) del XML CFDI.
 */
public function extraerConceptosCfdi(string $contenido): array
{
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($contenido);

    if ($xml === false) {
        throw new Exception('XML inválido.');
    }

    $ns = $xml->getNamespaces(true);
    $cfdiNs = $ns['cfdi'] ?? 'http://www.sat.gob.mx/cfd/4';

    $conceptosArray = [];

    $conceptos = $xml->children($cfdiNs)->Conceptos;

    if (!$conceptos) {
        return [];
    }

    foreach ($conceptos->Concepto as $concepto) {
        $attr = $concepto->attributes();

        $conceptosArray[] = [
            'descripcion' => (string) ($attr['Descripcion'] ?? ''),
            'cantidad' => (float) ($attr['Cantidad'] ?? 0),
            'valor_unitario' => (float) ($attr['ValorUnitario'] ?? 0),
            'importe' => (float) ($attr['Importe'] ?? 0),
        ];
    }

    return $conceptosArray;
}

/**
 * Recupera conceptos de un cfdi a partir de un archivo cargado 
 */
public function extraerConceptosUploadFile(UploadedFile $archivo): array
{
    $contenido = file_get_contents($archivo->getRealPath());

    return $this->extraerConceptosCfdi($contenido);
}

/**
 * Recupera conceptos a partir de un archivo almacenado en el servidor
 */

public function extraerConceptosDesdeRuta(string $ruta): array
{
    if (!file_exists($ruta)) {
        throw new Exception("El archivo no existe en la ruta: {$ruta}");
    }

    $contenido = file_get_contents($ruta);

    return $this->extraerConceptosCfdi($contenido);
}
}