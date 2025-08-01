<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Compras\Http\Requests\UploadDocsOCRequest;
use Illuminate\Http\Request;
//Model
use Modules\Compras\Models\DocumentosOrdenesCompra;

//Utilities 
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Modules\Compras\Models\DocumentosFactura;
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
        $registro = DocumentosOrdenesCompra::where('orden_compra_id', $id)->get();
        return $registro;
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
        $rutas = DocumentosOrdenesCompra::where('orden_compra_id', $id)
            ->get(['ruta_xml_factura', 'ruta_pdf_factura', 'complemento_pago_xml', 'complemento_pago_pdf', 'comprobante_pago']);

        $zip = new ZipArchive();
        $zipFileName = 'facturas_' . $id . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($rutas as $ruta) {
                $archivos = [
                    $ruta['ruta_pdf_factura'],
                    $ruta['ruta_xml_factura'],
                    $ruta['complemento_pago_pdf'],
                    $ruta['complemento_pago_xml'],
                    $ruta['comprobante_pago'],
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
     * Recupera los datos del xml y los procesa listos para mostrar en el backend
     * 
     * @param id  de la orden de compra
     */
    public function leerYProcesarXML($id)
    {
        // Catalogo de códigos SAT
        $assetsJson = 'Modules/Compras/resources/assets/json';
        $catMP = File::get(base_path($assetsJson . '/catalogoSAT/metodoPago.json'));
        $catRF = File::get(base_path($assetsJson . '/catalogoSAT/regimenFiscal.json'));
        $catUC = File::get(base_path($assetsJson . '/catalogoSAT/usoCFDI.json'));
        $catTC = File::get(base_path($assetsJson . '/catalogoSAT/tipoComprobante.json'));


        $jsonMP = json_decode(json: $catMP, associative: true);
        $jsonRF = json_decode(json: $catRF, associative: true);
        $jsonUCfdi = json_decode(json: $catUC, associative: true);
        $jsonTC = json_decode(json: $catTC, associative: true);
        // $dataFacturacion = $json[$data['destino'][0]->empresa];

        $rutas = DocumentosOrdenesCompra::where('orden_compra_id', $id)->get(['id', 'ruta_xml_factura']);

        $factura = [
            'comprobantes' => [],
            'impuestos' => [],
            'emisor' => [],
            'receptor' => [],
            'metodoPago' => [],
            'sumaSubTotal' => 0,
            'sumaTotal' => 0,
        ];

        $ns = 'http://www.sat.gob.mx/cfd/4';

        foreach ($rutas as $index => $ruta) {
            $rutaXML = storage_path('app/' . $ruta['ruta_xml_factura']);
            if (!file_exists($rutaXML)) {
                return response()->json(['message' => 'Archivo no encontrado: ' . $ruta['ruta_xml_factura']], 404);
            }

            $contenidoXML = file_get_contents($rutaXML);
            $xml = new \SimpleXMLElement($contenidoXML);

            $xml->registerXPathNamespace('cfdi', $ns);


            $comprobante = $xml->xpath('//cfdi:Comprobante')[0] ?? null;

            if ($comprobante) {

                $subtotal = (float) $comprobante['SubTotal'];
                $total = (float) $comprobante['Total'];
                $factura['comprobantes'][] = [
                    'idRuta' => $ruta['id'],
                    'fecha' => (string) $comprobante['Fecha'],
                    'folio' => (string) $comprobante['Folio'],
                    'serie' => (string) $comprobante['Serie'],
                    'subTotal' => $subtotal,
                    // 'impuestos' =>  $impuestos['TotalImpuestosTrasladados'],
                    'tComprobante' =>  (string) $comprobante['TipoDeComprobante'],
                    'tComprobanteDesc' => $jsonTC[(string) $comprobante['TipoDeComprobante']]['descripcion'],
                    'moneda' => (string) $comprobante['Moneda'],
                    'total' => $total,
                ];

                $factura['sumaSubTotal'] += $subtotal;
                $factura['sumaTotal'] += $total;

                if ($index === 0) {
                    $factura['metodoPago'] = [
                        'metodoPago' => (string) $comprobante['MetodoPago'],
                        'metodoPagoDesc' => $jsonMP[(string) $comprobante['MetodoPago']]['descripcion'],
                    ];
                }
            }

            $impuestos = $xml->xpath('//cfdi:Impuestos') ?? null;
            // $factura['impuestos'][] = [$impuestos];

            foreach ($impuestos as $impuesto) {
                if (isset($impuesto['TotalImpuestosTrasladados'])) {
                    $factura['impuestos'][] =  $impuesto['TotalImpuestosTrasladados'];
                }
            }

            if ($index === 0) {
                $emisor = $xml->xpath('//cfdi:Emisor')[0] ?? null;
                if ($emisor) {
                    $factura['emisor'] = [
                        'rfc' => (string) $emisor['Rfc'],
                        'nombre' => (string) $emisor['Nombre'],
                        'regimenFiscal' => (string) $emisor['RegimenFiscal'],
                        'regimenFiscalDesc' => $jsonRF[(string) $emisor['RegimenFiscal']]['descripcion'],
                    ];
                }

                $receptor = $xml->xpath('//cfdi:Receptor')[0] ?? null;
                if ($receptor) {
                    $factura['receptor'] = [
                        'rfc' => (string) $receptor['Rfc'],
                        'nombre' => (string) $receptor['Nombre'],
                        'usoCFDI' => (string) $receptor['UsoCFDI'],
                        'usoCFDIDesc' => $jsonUCfdi[(string) $receptor['UsoCFDI']]['descripcion'],
                        'domicilioFiscalReceptor' => (string) $receptor['DomicilioFiscalReceptor'],
                    ];
                }
            }
        }

        return response()->json(['factura' => $factura], 200);
    }


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

    
    public function subirDocumento(Request $request)
    {
        $data = $request;
        $hoy = date("jnY"); //Recuperar la fecha del dia de hoy para diferenciar el registro nuevo
        $time = time(); //Marca temporal del momento en el que se subió

        $carpetaOrdenCompra = 'docsOrdenCompra/' . $data['orden_compra_id'];
        Storage::makeDirectory($carpetaOrdenCompra);

        $docsFactura = new DocumentosFactura();
        $docsFactura->tipo_documento = $data['tipo_documento'];

        if ($data->hasFile('archivo_xml')) {
            $nombreArchivo = $data['tipo_documento'] . $hoy . $time . "." . $data->file('archivo_xml')->getClientOriginalExtension();
            $docsFactura->archivo_xml = $data->file('archivo_xml')->storeAs($carpetaOrdenCompra, $nombreArchivo);
        }
        if ($data->hasFile('archivo')) {
            $nombreArchivo = $data['tipo_documento'] . $hoy . $time . "." . $data->file('archivo')->getClientOriginalExtension();
            $docsFactura->archivo = $data->file('archivo')->storeAs($carpetaOrdenCompra, $nombreArchivo);
        }
        $docsFactura->com_documentos_ordenes_compra_id = $data['idFactura'];
        $docsFactura->fecha = date('Y-m-d H:i:s') ?? now();
        $docsFactura->save();

        return response()->json([
            'status' => 'succes',
            'message' => 'Documento guardado correctamente',
            'data' => []
        ]);
    }
}
