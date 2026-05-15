<?php

namespace Modules\Compras\Services;

use Illuminate\Http\UploadedFile;
use Exception;
use Modules\Compras\Models\DetalleSolicitud;

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
            'clave_prod_serv' => (string) ($attr['ClaveProdServ'] ?? ''),
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

/**
 * Determina si una entrega es parcial comparando los conceptos de la factura
 * con los detalles confirmados de la solicitud de compra.
 *
 * Una entrega se considera parcial cuando la factura tiene menos conceptos
 * que los detalles confirmados en la solicitud original.
 */
public function isEntregaTotal($idSolicitud, array $conceptosFactura){
    $detalles = DetalleSolicitud::where('solicitudes_compra_id', $idSolicitud)
        ->with('unidadMedida')
        ->confirmadas()
        ->get();
    
    $numeroDetalles = count($detalles);
    $numeroConceptosFactura = count($conceptosFactura);

    $isEntregaParcial = true;

    if($numeroConceptosFactura < $numeroDetalles){
        $isEntregaParcial = false;
    }

    return $isEntregaParcial;
}

/**
 * Compara los detalles de una solicitud de compra contra los conceptos de una factura,
 * determinando el estatus de facturación de cada artículo solicitado.
 *
 * Para cada detalle de la solicitud, busca el concepto de factura más similar
 * usando un score de similitud textual. Con base en las cantidades, clasifica
 * cada detalle como: FACTURADO_COMPLETO, FACTURADO_PARCIAL o NO_FACTURADO.
 */
public function compararFacturacion($idSolicitud, array $conceptosFactura)
{
    $detalles = DetalleSolicitud::where('solicitudes_compra_id', $idSolicitud)
        ->with('unidadMedida')
        ->confirmadas()
        ->get();

    $resultado = [];

    foreach ($detalles as $detalle) {

        $mejorCoincidencia = null;
        $mejorScore = 0;

        foreach ($conceptosFactura as $concepto) {

            $score = $this->calcularSimilitud(
                $detalle->descripcion,
                $concepto['descripcion']
            );

            // sumar puntos si coincide marca/modelo/etc

            $descripcionFactura = $this->normalizarTexto($concepto['descripcion']);
            $descripcionDetalle = $this->normalizarTexto($detalle->descripcion);

            // Detectar medidas
            preg_match('/\d+/', $descripcionFactura, $numFactura);
            preg_match('/\d+/', $descripcionDetalle, $numDetalle);

            if (
                isset($numFactura[0]) &&
                isset($numDetalle[0]) &&
                $numFactura[0] == $numDetalle[0]
            ) {
                $score += 1;
            }

            // Guardar mejor match
            if ($score > $mejorScore) {
                $mejorScore = $score;
                $mejorCoincidencia = $concepto;
            }
        }

        // Considerar válido solo si supera cierto score
        $cantidadFacturada = 0;
        $claveProdServ = null;
        $descripcionFactura = null;

        if ($mejorScore >= 1 && $mejorCoincidencia) {
            $cantidadFacturada = $mejorCoincidencia['cantidad'] ?? 0;
            $claveProdServ = $mejorCoincidencia['clave_prod_serv'] ?? null;
            $descripcionFactura = $mejorCoincidencia['descripcion'] ?? null;
        }

        $estatus = 'NO_FACTURADO';

        if ($cantidadFacturada >= $detalle->cantidad) {
            $estatus = 'FACTURADO_COMPLETO';
        } elseif ($cantidadFacturada > 0) {
            $estatus = 'FACTURADO_PARCIAL';
        }

        $resultado[] = [
            'detalle_id' => $detalle->id,
            'descripcion_solicitud' => $detalle->descripcion,
            'descripcion_factura' => $descripcionFactura,
            'cantidad_solicitada' => $detalle->cantidad,
            'cantidad_facturada' => $cantidadFacturada,
            'clave_prod_serv' => $claveProdServ,
            'score_similitud' => $mejorScore,
            'faltante' => max(
                0,
                $detalle->cantidad - $cantidadFacturada
            ),
            'estatus' => $estatus,
        ];
    }

    return $resultado;
}

/**
* Normaliza un texto para facilitar comparaciones: lo convierte a minúsculas,
 * elimina acentos, caracteres especiales y espacios redundantes.
 */
private function normalizarTexto($texto)
{
    $texto = strtolower($texto);

    // Quitar acentos
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

    // Quitar caracteres especiales
    $texto = preg_replace('/[^a-z0-9\s]/', ' ', $texto);

    // Compactar espacios
    $texto = preg_replace('/\s+/', ' ', $texto);

    return trim($texto);
}

/**
 * Calcula un score de similitud entre dos textos contando las palabras
 * significativas que tienen en común (palabras de más de 2 caracteres).
 */
private function calcularSimilitud($texto1, $texto2)
{
    $texto1 = $this->normalizarTexto($texto1);
    $texto2 = $this->normalizarTexto($texto2);

    $palabras1 = collect(explode(' ', $texto1))
        ->filter(fn($p) => strlen($p) > 2);

    $palabras2 = collect(explode(' ', $texto2))
        ->filter(fn($p) => strlen($p) > 2);

    $coincidencias = $palabras1->intersect($palabras2)->count();

    return $coincidencias;
}
}