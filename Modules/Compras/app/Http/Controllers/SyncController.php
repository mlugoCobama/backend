<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Modules\Compras\Models\DocumentosOrdenesCompra;

class SyncController extends Controller
{
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    
    {
        $archivos = DB::select(
            'CALL SP_GetFacturasEmpresas(?)',
            [$id]
        );

        return response()->json([
            'intercompania' => $id,
            'total' => count($archivos),
            'archivos' => collect($archivos)->map(function ($archivo) {
                return [
                    'id'     => $archivo->id_doc,
                    'nombre' => $archivo->orden_compra,
                    // 'tipo'   => 'xml',
                    // URL firmada (recomendado)
                    'urlXML' => URL::temporarySignedRoute(
                        'archivosXML.stream',
                        now()->addMinutes(60),
                        ['archivoId' => $archivo->id_doc]
                    ),
                    'urlPDF' => URL::temporarySignedRoute(
                        'archivosPDF.stream',
                        now()->addMinutes(60),
                        ['archivoId' => $archivo->id_doc]
                    )
                ];
            })
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

        return view('compras::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    # Modificar esto ya casi queda 
    public function streamArchivoXML(Request $request, $archivoId)
    {
        if (!$request->hasValidSignature()) {
            return response()->json([
                'documento_id' => $archivoId,
                'archivos' => [],
                'mensaje' => 'Firma invalida'
            ]);

            // abort(401, 'URL no válida o expirada');
        }

        // $archivo =  DB::selectOne(
        //     'SELECT ruta_xml_factura
        //      FROM com_documentos_ordenes_compra 
        //      WHERE id = ?',
        //     [$archivoId]
        // );

        $archivo = DocumentosOrdenesCompra::find($archivoId);

        if (!$archivo || !Storage::exists($archivo->ruta_xml_factura)) {
            return response()->json([
                'documento_id' => $archivoId,
                'archivos' => [],
                'mensaje' => 'No hay archivos'
            ]);

            // abort(404);
        }


        $archivo->sync = 1;
        $archivo->syncned_at = now();
        $archivo->save();
        
        return Storage::download(
            $archivo->ruta_xml_factura,
            // $archivo->nombre_archivo
        );
    }

    public function streamArchivoPDF(Request $request, $archivoId)
    {
        if (!$request->hasValidSignature()) {
            return response()->json([
                'documento_id' => $archivoId,
                'archivos' => [],
                'mensaje' => 'Firma invalida'
            ]);

            // abort(401, 'URL no válida o expirada');
        }

        $archivo = DB::selectOne(
            'SELECT ruta_pdf_factura
             FROM com_documentos_ordenes_compra 
             WHERE id = ?',
            [$archivoId]
        );

        if (!$archivo || !Storage::exists($archivo->ruta_pdf_factura)) {
            return response()->json([
                'documento_id' => $archivoId,
                'archivos' => [],
                'mensaje' => 'No hay archivos'
            ]);

            // abort(404);
        }

        return Storage::download(
            $archivo->ruta_pdf_factura,
            // $archivo->nombre_archivo
        );
    }
}
