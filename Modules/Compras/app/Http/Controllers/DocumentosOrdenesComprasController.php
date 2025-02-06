<?php

namespace Modules\Compras\Http\Controllers;

use Illuminate\Support\Facades\File;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\Http\Response;

use Illuminate\Support\Facades\Storage;
use ZipArchive;

use Modules\Compras\Models\DocumentosOrdenesCompra;

class DocumentosOrdenesComprasController extends Controller
{
    //Recupera documentos de ordenes de compra
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


    // Genera un ZIP con la factura en XML y PDF para ser descargado
    //RECORRE LAS RUTAS Y GUARDAR UNO POR UNO EN EL ZIP
    public function downloadFacturas($id)
    {
        $rutas = DocumentosOrdenesCompra::where('orden_compra_id', $id)->get(['ruta_xml_factura', 'ruta_pdf_factura']);
        $zip = new ZipArchive();
        $zipFileName = 'facturas_' . $id . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {

            foreach ($rutas as $ruta) {
                $archivoPDF = $ruta['ruta_pdf_factura'];
                $archivoXML = $ruta['ruta_xml_factura'];
                if (Storage::exists($archivoPDF)) {
                    $zip->addFile(Storage::path($archivoPDF), basename($archivoPDF));
                }
                if (Storage::exists($archivoXML)) {
                    $zip->addFile(Storage::path($archivoXML), basename($archivoXML));
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
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('compras::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('compras::create');
    }

    /**
     * Almacena los archivos de orden compra
     */
    public function store(Request $request)
    {
        $data = $request;
        $hoy = date("jnY");
        $time = time();
        $docsOrdenCompra = new DocumentosOrdenesCompra();

        $carpetaOrdenCompra = 'docsOrdenCompra/' . $data['orden_compra_id'];
        Storage::makeDirectory($carpetaOrdenCompra);

        if ($data->hasFile('factura_xml')) {
            $nombreArchivo = "factura_xml" . $hoy .$time ."." . $data->file('factura_xml')->getClientOriginalExtension(); 
            $docsOrdenCompra->ruta_xml_factura = $data->file('factura_xml')->storeAs($carpetaOrdenCompra, $nombreArchivo); 
        }
        if ($data->hasFile('factura_pdf')) {
            $nombreArchivo = "factura_pdf" . $hoy . $time . "." . $data->file('factura_pdf')->getClientOriginalExtension();
            $docsOrdenCompra->ruta_pdf_factura = $data->file('factura_pdf')->storeAs($carpetaOrdenCompra, $nombreArchivo);
        }
        if ($data->hasFile('comprobante_pago')) {

            $nombreArchivo = "comprobante_pago" . $hoy . $time . "." . $data->file('comprobante_pago')->getClientOriginalExtension();
            $docsOrdenCompra->comprobante_pago = $data->file('comprobante_pago')->storeAs($carpetaOrdenCompra, $nombreArchivo);
        }
        $docsOrdenCompra->orden_compra_id = $data["orden_compra_id"];
        $docsOrdenCompra->fecha = $data["fecha"];

        $docsOrdenCompra->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha guardado correctamente',
            //'data' => new ProveedoresResource($proveedor),
            'data' => []
        ]);

        // return $carpetaOrdenCompra;
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $registro = DocumentosOrdenesCompra::where('orden_compra_id', $id)->get();
        return $registro;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('compras::edit');
    }

    /**
     * Guarda los documentos de orden de compra
     */
    public function update(Request $request, $id)
    {
        $data = $request;
        $hoy = date("jnY"); //Recuperar la fecha del dia de hoy para diferenciar el registro nuevo
        $registro = DocumentosOrdenesCompra::where('id', $id)->first();

        $carpetaOrdenCompra = 'docsOrdenCompra/' . $id;
        Storage::makeDirectory($carpetaOrdenCompra);

        if ($data->hasFile('factura_xml')) {

            $archivoEliminar = $registro->ruta_xml_factura; //Recupera el anterior ruta del archivo al a eliminar
            if ($archivoEliminar) {
                Storage::delete($archivoEliminar);
            }
            $nombreArchivo = "factura_xml" . $hoy . "." . $data->file('factura_xml')->getClientOriginalExtension(); //Asigna un nuevo nombre al archivo
            $registro->ruta_xml_factura = $data->file('factura_xml')->storeAs($carpetaOrdenCompra, $nombreArchivo); //Actualiza la ruta y el archivo
        }
        if ($data->hasFile('factura_pdf')) {

            $archivoEliminar = $registro->ruta_pdf_factura;

            if ($archivoEliminar) {
                Storage::delete($archivoEliminar);
            }

            $nombreArchivo = "factura_pdf" . $hoy . "." . $data->file('factura_pdf')->getClientOriginalExtension();
            $registro->ruta_pdf_factura = $data->file('factura_pdf')->storeAs($carpetaOrdenCompra, $nombreArchivo);
        }
        if ($data->hasFile('comprobante_pago')) {

            $archivoEliminar = $registro->comprobante_pago;

            if ($archivoEliminar) {
                Storage::delete($archivoEliminar);
            }

            $nombreArchivo = "comprobante_pago" . $hoy . "." . $data->file('comprobante_pago')->getClientOriginalExtension();
            $registro->comprobante_pago = $data->file('comprobante_pago')->storeAs($carpetaOrdenCompra, $nombreArchivo);
        }

        $registro->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha actualizado correctamente',
            'data' => $registro
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
