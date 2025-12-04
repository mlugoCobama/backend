<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Complementos;
use Modules\Compras\Http\Requests\UploadDocsOCRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
//Model
use Modules\Compras\Models\DocumentosOrdenesCompra;

use App\Enums\EstatusOrdenCompra;
use App\Enums\EstatusSolicitud;
use App\Mail\PagoOrdenCompra;
//Utilities
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\Compras\Http\Requests\SubirDocumentoRequest;
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\DocumentosFactura;
use Modules\Compras\Models\OrdenCompra;
use Modules\Compras\Models\SolicitudesCompra;
use ZipArchive;


class DocumentosOrdenesComprasController extends Controller
{

    /** ****************************************************
     * Almacena los archivos de orden compra
     ******************************************************/
    public function store(UploadDocsOCRequest $request)
    {
        try {
            $data = $request;
            $hoy = date("jnY");
            $time = time();
            $docsOrdenCompra = new DocumentosOrdenesCompra();

            $carpetaOrdenCompra = 'docsOrdenCompra/' . $data['orden_compra_id'];
            Storage::makeDirectory($carpetaOrdenCompra);

            $documentos = ['factura_xml', 'factura_pdf', 'comprobante_pago', 'complemento_pago_xml', 'complemento_pago_pdf'];
            $keys = ['ruta_xml_factura', 'ruta_pdf_factura', 'comprobante_pago', 'complemento_pago_xml', 'complemento_pago_pdf'];

            for ($i = 0; $i < count($documentos); $i++) {

                if ($data->hasFile($documentos[$i])) {
                    $nombreArchivo = $documentos[$i] . $hoy . $time . "." . $data->file($documentos[$i])->getClientOriginalExtension();
                    $docsOrdenCompra->{$keys[$i]} = $data->file($documentos[$i])->storeAs($carpetaOrdenCompra, $nombreArchivo);
                }
            }

            $docsOrdenCompra->orden_compra_id = $data["orden_compra_id"];
            $docsOrdenCompra->fecha = date('Y-m-d H:i:s');

            $docsOrdenCompra->save();

            if($data->hasFile('comprobante_pago')){
                $orden = OrdenCompra::find($data["orden_compra_id"]);
                if($orden->modo_pago == 1 ){
                    $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::PAGADA, EstatusSolicitud::PAGADA);
                    $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::EN_SURTIDO, EstatusSolicitud::EN_SURTIDO);
                    $this->enviarCorreoPago($orden, $docsOrdenCompra->comprobante_pago);    
                    $controlerOC = new OrdenesComprasController;
                    $controlerOC->enviarCorreoSurtido($orden->id);      
                         
                }else{
                    $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::PAGADA, EstatusSolicitud::PAGADA);
                    $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::CARGA_COMPLEMENTO, EstatusSolicitud::CARGA_COMPLEMENTO);
                    $this->enviarCorreoPago($orden, $docsOrdenCompra->comprobante_pago);
                }
                
            }

            if($data->hasFile('factura_xml') && $data->hasFile('factura_pdf')){
                $orden = OrdenCompra::find($data["orden_compra_id"]);
                if($orden->modo_pago == 1 ){
                    $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::FACTURADO, EstatusSolicitud::FACTURADO);
                }else{
                    $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::FACTURADO, EstatusSolicitud::FACTURADO);
                    $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::SOLICITADO_PAGO, EstatusSolicitud::SOLICITADO_PAGO);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha guardado correctamente',
                'data' => []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al guardar los documentos',
                'error' => $e->getMessage()
            ]);
        }



        // return $carpetaOrdenCompra;
    }

    /** *****************************************************
     * Recupera los documentos de orden de compra
     * en base al id de orden de compra
     *******************************************************/
    public function show($id)
    {
        // $registro = DocumentosOrdenesCompra::where('orden_compra_id', $id)->get();

        // $rutas = DocumentosOrdenesCompra::where('orden_compra_id', $id)->get(['id', 'fecha', 'ruta_xml_factura as xml', 'ruta_pdf_factura as representacion_impresa']);
        $rutasQuery = DB::table('com_documentos_ordenes_compra')
            ->select([
                'id',
                'fecha',
                DB::raw("'factura' as tipo_documento"),
                'ruta_pdf_factura as representacion_impresa',
                'ruta_xml_factura as xml'
            ])
            ->where('orden_compra_id', $id)
            ->whereNotNull('ruta_pdf_factura')
            ->where('ruta_pdf_factura', '!=', '');

        // Segundo conjunto: comprobante
        $comprobanteQuery = DB::table('com_documentos_ordenes_compra')
            ->select([
                'id',
                'fecha',
                DB::raw("'comprobante_pago' as tipo_documento"),
                'comprobante_pago as representacion_impresa',
                DB::raw("'' as xml")
            ])
            ->where('orden_compra_id', $id)
            ->whereNotNull('comprobante_pago')
            ->where('comprobante_pago', '!=', '');
        $rutas = $rutasQuery->union($comprobanteQuery)->get();

        $rutaIds = $rutas->pluck('id')->toArray();

        $docsFactura = DocumentosFactura::whereIn('com_documentos_ordenes_compra_id', $rutaIds)->get(['id', 'fecha', 'tipo_documento', 'xml', 'representacion_impresa']);

        $registros = $rutas->concat($docsFactura);

        return $registros;
    }


    /** **************************************************
     * Guarda los documentos de orden de compra
     * Facturas XML, Facturas PDF, Comprobantes de pago
     ****************************************************/
    public function update(UploadDocsOCRequest $request, $id)
    {
        $registro = DocumentosOrdenesCompra::where('id', $id)->first();
        if (!$registro) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'error' => 'El registro al que se intenta acceder no existe'
            ]);
        }
        try {
            $data = $request;
            $hoy = date("jnY"); //Recuperar la fecha del dia de hoy para diferenciar el registro nuevo
            $time = time(); //Marca temporal del momento en el que se subió


            $carpetaOrdenCompra = 'docsOrdenCompra/' . $data['orden_compra_id'];
            Storage::makeDirectory($carpetaOrdenCompra);

            $documentos = ['factura_xml', 'factura_pdf', 'comprobante_pago', 'complemento_pago_xml', 'complemento_pago_pdf'];
            $keys = ['ruta_xml_factura', 'ruta_pdf_factura', 'comprobante_pago', 'complemento_pago_xml', 'complemento_pago_pdf'];

            for ($i = 0; $i < count($documentos); $i++) {
                if ($data->hasFile($documentos[$i])) {

                    $archivoEliminar = $registro->{$keys[$i]}; //Recupera el anterior ruta del archivo al a eliminar
                    if ($archivoEliminar) {
                        Storage::delete($archivoEliminar);
                    }
                    $nombreArchivo = $documentos[$i] . $hoy . $time . "." . $data->file($documentos[$i])->getClientOriginalExtension(); //Asigna un nuevo nombre al archivo
                    $registro->{$keys[$i]} = $data->file($documentos[$i])->storeAs($carpetaOrdenCompra, $nombreArchivo); //Actualiza la ruta y el archivo
                }
            }

            $registro->save();

            if($data->hasFile('comprobante_pago')){
                $orden = OrdenCompra::find($data["orden_compra_id"]);
                if($orden->modo_pago == 1 ){
                    $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::PAGADA, EstatusSolicitud::PAGADA);
                    $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::EN_SURTIDO, EstatusSolicitud::EN_SURTIDO);
                    $controlerOC = new OrdenesComprasController;    
                    $controlerOC->enviarCorreoSurtido($orden->id);      
                    $this->enviarCorreoPago($orden, $registro->comprobante_pago);   
                }else{
                    $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::PAGADA, EstatusSolicitud::PAGADA);
                    $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::CARGA_COMPLEMENTO ,EstatusSolicitud::CARGA_COMPLEMENTO);
                    $this->enviarCorreoPago($orden, $registro->comprobante_pago);  
                }
                
            }

            if($data->hasFile('factura_xml') && $data->hasFile('factura_pdf')){
                $orden = OrdenCompra::find($data["orden_compra_id"]);
                if($orden->modo_pago == 1 ){
                    $this->actStatusOrdenSolicitud($data['orden_compra_id'],EstatusOrdenCompra::FACTURADO, EstatusSolicitud::FACTURADO);
                }else{
                    $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::FACTURADO, EstatusSolicitud::FACTURADO);
                    $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::SOLICITADO_PAGO, EstatusSolicitud::SOLICITADO_PAGO);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha actualizado correctamente',
                'data' => $registro
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrio un error al guardar el archivo',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        //
    }

    public function leerYProcesarXML($id)
    {
        // $rutas = DocumentosOrdenesCompra::where('orden_compra_id', $id)->get(['id', 'fecha', 'ruta_xml_factura as xml', 'ruta_pdf_factura as representacion_impresa']);

        // $comprobante = DocumentosOrdenesCompra::where('orden_compra_id', $id)
        // ->first([
        //     'id',
        //     'fecha',
        //     DB::raw("'comprobante_pago' as tipo_documento"),
        //     'comprobante_pago as representacion_impresa',
        //     'ruta_xml_factura as xml'
        // ]);

        // Primer conjunto: rutas
        $rutasQuery = DB::table('com_documentos_ordenes_compra')
            ->select([
                'id',
                'fecha',
                DB::raw("'factura' as tipo_documento"),
                'ruta_pdf_factura as representacion_impresa',
                'ruta_xml_factura as xml'
            ])
            ->where('orden_compra_id', $id)
            ->whereNotNull('ruta_pdf_factura')
            ->where('ruta_pdf_factura', '!=', '');

        // Segundo conjunto: comprobante
        $comprobanteQuery = DB::table('com_documentos_ordenes_compra')
            ->select([
                'id',
                'fecha',
                DB::raw("'comprobante_pago' as tipo_documento"),
                'comprobante_pago as representacion_impresa',
                DB::raw("'' as xml")
            ])
            ->where('orden_compra_id', $id)
            ->whereNotNull('comprobante_pago')
            ->where('comprobante_pago', '!=', '');




        // Unión de ambas consultas
        $documentos = $rutasQuery->union($comprobanteQuery)->get();
        $rutas = json_decode(json_encode($documentos), true);


        $rutaIds = $documentos->pluck('id')->toArray();

        $docsFactura = DocumentosFactura::whereIn('com_documentos_ordenes_compra_id', $rutaIds)->get(['id', 'fecha', 'tipo_documento', 'xml', 'representacion_impresa']);

        $factura = $this->leerComprobante($rutas, 'xml');

        // $uuidOriginal = $factura['comprobantes'][0]['UUID'];
        $uuidOriginal = $factura['UUIDs'];

        $docsFacturaProcesados = $this->leerComprobante($docsFactura, 'xml', $uuidOriginal);

        // Fusionar comprobantes
        $comprobantesFactura = $factura['comprobantes'];
        $comprobantesDocs = $docsFacturaProcesados['comprobantes'] ?? [];

        $uuidFactura = $factura['UUIDs'];
        $uuidsDocs = $docsFacturaProcesados['UUIDs'] ?? [];

        $factura['comprobantes'] = array_merge($comprobantesFactura, $comprobantesDocs);
        $factura['UUIDs'] = array_merge($uuidFactura, $uuidsDocs);
        return response()->json([
            'factura' => $factura,
        ], 200);
    }

    /**
     * Recupera un archivo relacionado con una orden de compra desde el servidor.
     *
     * @param int $id ID de la orden de compra.
     * @param string $file Nombre del archivo que se desea recuperar.
     * @return \Illuminate\Http\Response Archivo solicitado como respuesta HTTP con cabecera Content-Type.
     */
    public function getFile($id, $file)
    {
        $path = storage_path("app/docsOrdenCompra/$id/$file");
        if (!File::exists($path)) {
            abort(404);
        }
        $fileContent = File::get($path);
        $type = File::mimeType($path);
        return response($fileContent, 200)->header("Content-Type", $type);
    }


    /**
     * Genera un archivo ZIP que contiene las facturas en formato PDF y XML de una orden de compra específica.
     *
     * @param int $id ID de la orden de compra.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse Archivo ZIP generado, listo para descargar.
     */
    public function downloadFacturas($id)
    {

        $rutas = DocumentosOrdenesCompra::where('orden_compra_id', $id)->get(['id', 'fecha', 'ruta_xml_factura as xml', 'ruta_pdf_factura as representacion_impresa']);
        $rutaIds = $rutas->pluck('id')->toArray();
        $docsFactura = DocumentosFactura::whereIn('com_documentos_ordenes_compra_id', $rutaIds)->get(['id', 'fecha', 'tipo_documento', 'xml', 'representacion_impresa']);
        
        $docs = $rutas->concat($docsFactura);


        $zip = new ZipArchive();
        $zipFileName = 'facturas_' . $id . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($docs as $ruta) {
                $archivos = [
                    $ruta['xml'],
                    // $ruta['ruta_xml_factura'],
                    $ruta['representacion_impresa'],
                    // $ruta['complemento_pago_xml'],
                    // $ruta['comprobante_pago'],
                ];

                foreach ($archivos as $archivo) {
                    if (!is_null($archivo) && Storage::exists($archivo)) {
                        $zip->addFile(Storage::path($archivo), basename($archivo));
                    }
                }
            }
            $zip->close();
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo generar el archivo ZIP'
            ], 500);
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }


    /**
     * Recupera los datos del XML y los procesa listos para mostrar en el backend.
     *
     * @param array $rutas Datos que se obtienen de la consulta.
     * @param string $key Campo del cual se obtiene la ruta.
     * @param array $id UUIDs de las facturas cargadas.
     *
     * @return array Datos procesados listos para mostrar.
     */
    public function leerComprobante($rutas, $key, $id = null)
    {
        // Carga de catálogos SAT
        $assetsJson = 'Modules/Compras/resources/assets/json';
        $catMP = File::get(base_path("$assetsJson/catalogoSAT/metodoPago.json"));
        $catRF = File::get(base_path("$assetsJson/catalogoSAT/regimenFiscal.json"));
        $catUC = File::get(base_path("$assetsJson/catalogoSAT/usoCFDI.json"));
        $catTC = File::get(base_path("$assetsJson/catalogoSAT/tipoComprobante.json"));

        $jsonMP = json_decode($catMP, true);
        $jsonRF = json_decode($catRF, true);
        $jsonUCfdi = json_decode($catUC, true);
        $jsonTC = json_decode($catTC, true);

        // Inicializar estructura de respuesta
        $factura = [
            'comprobantes'    => [],
            // 'complementos'    => [],
            'impuestos'       => [],
            'emisor'          => [],
            'receptor'        => [],
            'metodoPago'      => [],
            'sumaSubTotal'    => 0,
            'sumaTotal'       => 0,
            'UUIDs'           => [],
        ];

        $nsCfdi = 'http://www.sat.gob.mx/cfd/4';
        $nsTfd  = 'http://www.sat.gob.mx/TimbreFiscalDigital';
        $nsPago = 'http://www.sat.gob.mx/Pagos20';


        foreach ($rutas as $index => $ruta) {
            // Valida que la tenga un archivo xml que pueda leer
            if ($ruta[$key] != null && !empty($ruta[$key])) {
                $rutaXML = storage_path('app/' . $ruta[$key]);
                if (!file_exists($rutaXML)) {
                    return response()->json([
                        'message' => 'Archivo no encontrado: ' . $ruta[$key]
                    ], 404);
                }

                $contenidoXML = file_get_contents($rutaXML);
                $xml = new \SimpleXMLElement($contenidoXML);

                //  Registrar namespaces antes de cualquier xpath()
                $xml->registerXPathNamespace('cfdi', $nsCfdi);
                $xml->registerXPathNamespace('tfd', $nsTfd);
                $xml->registerXPathNamespace('pago20', $nsPago);

                // Timbre fiscal
                $timbres = $xml->xpath('//cfdi:Complemento/tfd:TimbreFiscalDigital');
                $uuid = null;

                if (!empty($timbres)) {
                    $uuid = (string) $timbres[0]['UUID'];
                    $factura['UUIDs'][] = (string) $timbres[0]['UUID'];
                }

                // Datos comprobante principal
                $comprobante = $xml->xpath('//cfdi:Comprobante')[0] ?? null;
                if ($comprobante) {
                    $fecha    = (string) $comprobante['Fecha'];
                    $folio = (string) $comprobante['Folio'];
                    $subtotal = (float) $comprobante['SubTotal'];
                    $total    = (float) $comprobante['Total'];
                    $tipoComprobante = (string) $comprobante['TipoDeComprobante'];
                    $formaPago = (string) $comprobante['FormaPago'];
                    $serie = (string) $comprobante['Serie'];


                    $factura['comprobantes'][] = [
                        'idRuta'           => $ruta['id'] ?? null,
                        'fecha'            => $fecha,
                        'folio'            => $folio,
                        'serie'            => $serie,
                        'formaPago'        => $formaPago,
                        'subTotal'         => $tipoComprobante === 'E' ? $subtotal * -1 : $subtotal,
                        'tComprobante'     => (string) $comprobante['TipoDeComprobante'],
                        'tComprobanteDesc' => strtoupper($jsonTC[(string) $comprobante['TipoDeComprobante']]['descripcion']) ?? null,
                        'moneda'           => (string) $comprobante['Moneda'],
                        'total'            => $tipoComprobante === 'E' ? $total * -1 : $total,
                        'UUID'             => (string) $uuid ??  null,
                        'xml'                   => $ruta['xml'] ?? null,
                        'representacion_impresa' => $ruta['representacion_impresa'] ?? null,
                    ];

                    // $factura['sumaSubTotal'] += (float) $subtotal;
                    // $factura['sumaTotal']    += (float) $total;

                    $factura['sumaSubTotal'] += $tipoComprobante === 'E' ? $subtotal * -1 : $subtotal;
                    $factura['sumaTotal']    += $tipoComprobante === 'E' ? $total * -1 : $total;

                    if($index === 0){
                        $factura['metodoPago'] = [
                        'metodoPago'     => (string) $comprobante['MetodoPago'] ?? null,
                        'metodoPagoDesc' => $jsonMP[(string) $comprobante['MetodoPago']]['descripcion'] ?? null,
                        ];
                    }
                    
                    
                }

                // Datos de complementos de pagos
                $complementos = $xml->xpath('//cfdi:Complemento/pago20:Pagos/pago20:Pago');
                if (!empty($complementos)) {
                    foreach ($complementos as $complemento) {
                        // Registrar en el contexto de este nodo antes de xpath si se requiere
                        $complemento->registerXPathNamespace('pago20', $nsPago);

                        $fechaPago   = (string) $complemento['FechaPago'];
                        $monedaPago  = (string) $complemento['MonedaP'];
                        $formaPagoP  = (string) $complemento['FormaDePagoP'];

                        foreach ($complemento->xpath('pago20:DoctoRelacionado') as $doc) {
                            $serie           = (string) $doc['Serie'];
                            $folio           = (string) $doc['Folio'];
                            $numParcialidad  = (string) $doc['NumParcialidad'];
                            $impSaldoAnt     = (float) $doc['ImpSaldoAnt'];
                            $impPagado       = (float) $doc['ImpPagado'];
                            $uuid            = (string) $doc['IdDocumento'] ?: (string) $doc['UUID'] ?? null;

                            // if ($uuid === $id) {
                            if (is_array($id) && in_array($uuid, $id)) {
                                $factura['comprobantes'][] = [
                                    'idRuta'                => $ruta['id'] ?? null,
                                    'fecha'                 => $fechaPago,
                                    'folio'                 => $folio ?? "-",
                                    'serie'                 => $serie ?? "-",
                                    'subTotal'              => $impPagado ?? "-",
                                    'formaPago'             => $formaPagoP ?? "-",
                                    'tComprobante'          => (string) $comprobante['TipoDeComprobante'],
                                    'tComprobanteDesc'      => strtoupper($jsonTC[(string) $comprobante['TipoDeComprobante']]['descripcion'] . '-parc ' . $numParcialidad) ?? null,
                                    'moneda'                => $monedaPago ?? "-",
                                    'total'                 => $impSaldoAnt ?? "-",
                                    'UUID'                  => $uuid,
                                    'xml'                   => $ruta['xml'] ?? null,
                                    'representacion_impresa' => $ruta['representacion_impresa'] ?? null,
                                ];
                            }
                        }
                    }
                }

                // Impuestos
                $impuestos = $xml->xpath('//cfdi:Impuestos') ?? [];
                foreach ($impuestos as $impuesto) {
                    if (isset($impuesto['TotalImpuestosTrasladados'])) {
                        $factura['impuestos'][] = (float) $impuesto['TotalImpuestosTrasladados'];
                    }
                }

                // Datos emisor
                $emisor = $xml->xpath('//cfdi:Emisor')[0] ?? null;
                if ($emisor) {
                    $factura['emisor'] = [
                        'rfc'              => (string) $emisor['Rfc'],
                        'nombre'           => (string) $emisor['Nombre'],
                        'regimenFiscal'    => (string) $emisor['RegimenFiscal'],
                        'regimenFiscalDesc' => $jsonRF[(string) $emisor['RegimenFiscal']]['descripcion'] ?? null,
                    ];
                }

                // Datos receptor
                $receptor = $xml->xpath('//cfdi:Receptor')[0] ?? null;
                if ($receptor) {
                    $factura['receptor'] = [
                        'rfc'                    => (string) $receptor['Rfc'],
                        'nombre'                 => (string) $receptor['Nombre'],
                        'usoCFDI'                => (string) $receptor['UsoCFDI'],
                        'usoCFDIDesc'            => $jsonUCfdi[(string) $receptor['UsoCFDI']]['descripcion'] ?? null,
                        'domicilioFiscalReceptor' => (string) $receptor['DomicilioFiscalReceptor'] ?? null,
                    ];
                }
            } else {
                //Respuesta en el caso que no tenga un XML
                if( $ruta['tipo_documento'] == 'comprobante_pago'){
                    $factura['comprobantes'][] = [
                        'idRuta'                 => $ruta['id'] ?? null,
                        'fecha'                  => $ruta['fecha'],
                        'folio'                  => " - ",
                        'serie'                  => " - ",
                        'subTotal'               => 0,
                        'formaPago'              => " - ",
                        'tComprobante'           => " - ",
                        'tComprobanteDesc'       => (string) strtoupper(str_replace("_", " ", $ruta['tipo_documento'])),
                        'moneda'                 => " - ",
                        'total'                  => 0,
                        'UUID'                   => " - ",
                        'xml'                    => $ruta['xml'] ?? null,
                        'representacion_impresa' => $ruta['representacion_impresa'] ?? null,
                    ];
                }
                
            }
        }
        return $factura;
    }

    /**
    * Descarga un archivo directamente del servidor 
    * @param string $folder -> nombre del folder del tipo de documento en el storage ej: docsOrdenCompra
    * @param int $id -> id con el que se nombra la subcarpeta ej:  id_orden_compra: 2 
    * @param int $file -> archivo junto con su extension: factura_xml9820251754754870.xml 
    */
    public function downloadXML($folder, $id, $file)
    {
        $filePath = storage_path("app/$folder/$id/$file");

        $fileName = "$file";
        if (!File::exists($filePath)) {
            return response()->json([
                'error' => 'Archivo no encontrado',
            ], 404);
        }

        $type = File::mimeType($filePath);
        return response()->streamDownload(function () use ($filePath) {
            echo File::get($filePath);
        }, $fileName, [
            'Content-Type' => $type,
        ]);
    }


    /**
     * Sube y guarda documentos relacionados con una factura.
     *
     * @param Request $request -> debe contener:  
     *  'orden_compra_id': int, 'tipo_documento': string, 'archivo_xml': file, 'archivo': file, 'idFactura': int
     * @return response json 
     */
    public function subirDocumento(SubirDocumentoRequest $request)
    {
        $data = $request->validated();
        $hoy = date("jnY"); //Recuperar la fecha del dia de hoy para diferenciar el registro nuevo
        $time = time(); //Marca temporal del momento en el que se subió

        $carpetaOrdenCompra = 'docsOrdenCompra/' . $data['orden_compra_id'];
        Storage::makeDirectory($carpetaOrdenCompra);

        $docsFactura = new DocumentosFactura();
        $docsFactura->tipo_documento = $data['tipo_documento'];

        if ($request->hasFile('archivo_xml')) {
            $nombreArchivo = $data['tipo_documento'] . $hoy . $time . "." . $request->file('archivo_xml')->getClientOriginalExtension();
            $docsFactura->xml = $request->file('archivo_xml')->storeAs($carpetaOrdenCompra, $nombreArchivo);
        }
        if ($request->hasFile('archivo')) {
            $nombreArchivo = $data['tipo_documento'] . $hoy . $time . "." . $request->file('archivo')->getClientOriginalExtension();
            $docsFactura->representacion_impresa = $request->file('archivo')->storeAs($carpetaOrdenCompra, $nombreArchivo);
        }

        $docsFactura->com_documentos_ordenes_compra_id = $data['idFactura'];
        $docsFactura->fecha = date('Y-m-d H:i:s') ?? now();
        $docsFactura->save();

        if($data['tipo_documento'] ==  'complemento_pago'){
                $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::CARGA_COMPLEMENTO, EstatusSolicitud::CARGA_COMPLEMENTO);
            }

        if($data['tipo_documento'] ==  'comprobante_pago'){
                $orden = OrdenCompra::find($data["orden_compra_id"]);
        if($orden->modo_pago == 1 ){
            $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::PAGADA, EstatusSolicitud::PAGADA);
                }else{
                $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::PAGADA, EstatusSolicitud::PAGADA);
                $this->actStatusOrdenSolicitud($data['orden_compra_id'], EstatusOrdenCompra::CARGA_COMPLEMENTO, EstatusSolicitud::CARGA_COMPLEMENTO);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Documento guardado correctamente',
            'data' => []
        ]);
    }

    public function actStatusOrdenSolicitud($idOrdenCompra, $statusOrdenCompra, $estatusSolicitud){

        $orden = OrdenCompra::where('id', $idOrdenCompra)->first();
        if ($orden) {
            $orden->estatus = $statusOrdenCompra;
            if($statusOrdenCompra === EstatusOrdenCompra::EN_SURTIDO){
                $orden->surtido_solcitado = 1;
            }
            if($statusOrdenCompra === EstatusOrdenCompra::PAGADA){
                $orden->pagado = 1;
            }
            $orden->save(); 
        }

        $cotizacion = Cotizaciones::where('id', $orden->cotizaciones_id)->first();

        $solicitud = SolicitudesCompra::find($cotizacion->solicitudes_compra_id);
        if ($solicitud) {
            $solicitud->estatus = $estatusSolicitud;
            $solicitud->save(); 
        }

    }


    public function enviarCorreoPago($ordenCompra, $rutaPago)
    {       
        $cotizacion = Cotizaciones::find($ordenCompra->cotizaciones_id);
        if (!$cotizacion) {
            throw new \Exception('No se encontró la cotización asociada');
        }

        $proveedorSeleccionado = CotizacionesProveedores::where('cotizaciones_id', $cotizacion->id)
            ->Seleccionado()
            ->with(['datos_proveedor' => function ($query) {
                $query->select('id', 'nombre', 'correo');
            }])->first(['id', 'proveedores_id', 'seleccionado']);

        if (!$proveedorSeleccionado) {
            throw new \Exception('No hay un proveedor seleccionado para esta cotización');
        }

        $solicitudCompra = SolicitudesCompra::find($cotizacion->solicitudes_compra_id);
        if (!$solicitudCompra) {
            throw new \Exception('No se encontró la solicitud de compra');
        }

        $datos = [
            'ordenCompra' => $ordenCompra,
            'solicitudCompra' => $solicitudCompra,
            'cotizacion' => $cotizacion,
            'proveedor' => $proveedorSeleccionado,
        ];

        Mail::to($proveedorSeleccionado->datos_proveedor->correo)->send(new PagoOrdenCompra($datos, $rutaPago));
    }


}
