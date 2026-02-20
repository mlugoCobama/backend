<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Modules\Compras\Models\DocumentosOrdenesCompra;
use Modules\Compras\Models\OrdenCompra;

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
            'CALL SP_GetFacturasEmpresasTesting(?)',
            [$id]
        );

        return response()->json([
            'intercompania' => $id,
            'total' => count($archivos),
            'archivos' => collect($archivos)->map(function ($archivo) {
                return [
                    'folio' => $archivo->clave,
                    'proveedor' => $archivo->proveedor,
                    'importeTotal' => $archivo->importe_total,
                    'ordenDeCompra' => URL::temporarySignedRoute(
                        'ordenCompra.stream',
                        now()->addMinutes(60),
                        ['idSolicitudCompra' => $archivo->id_solicitud_compra]
                    ),
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


        // $archivo->sync = 1;
        // $archivo->syncned_at = now();
        // $archivo->save();
        
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

    public function streamOrdenCompra(Request $request, $idSolicitudCompra)
    {
        if (!$request->hasValidSignature()) {
            return response()->json([
                'ordenCompra' => $idSolicitudCompra,
                'archivos' => [],
                'mensaje' => 'Firma invalida'
            ]);

            // abort(401, 'URL no válida o expirada');
        }

        $ordenCompra = new OrdenesComprasController();
        $datosOrdenCompra = $ordenCompra->consultaDatosPDF($idSolicitudCompra);

        $pdf = $datosOrdenCompra['archivoPDF'];

        // return response()->streamDownload(function () use ($pdf) {
        //     echo $pdf;
        //     // $pdf->output();
        // }, "orden_compra_{$idSolicitudCompra}.pdf");

        $fileName = ''.$datosOrdenCompra['folioOrdenCompra'].'_'. $datosOrdenCompra['folioSolicitudCompra'].'.pdf';
        return response($pdf, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('X-Filename', $fileName)
            ->header('Access-Control-Expose-Headers', 'X-Filename');
    }


    public function confirmar(Request $request)
    {
        $archivos = $request->input('archivos');

        if (!is_array($archivos)) {
            return response()->json([
                'mensaje' => 'Formato inválido, se esperaba un listado de archivos'
            ], 400);
        }

        foreach ($archivos as $archivo) {
            $folio = $archivo['folio'] ?? null;
            $estado = $archivo['estado'] ?? null;

            if ($folio && $estado !== null) {

                preg_match('/OC-\d+/', $folio, $matches);
                $ordenCompra = $matches[0] ?? null;

                $registro = OrdenCompra::where('folio_oc', $ordenCompra)->first();
                $registro->sync_a3 = $estado;
                $registro->syncned_a3_at = Carbon::now()->toDateTimeString();
                $registro->save();
            }
        }

        return response()->json([
            'mensaje' => 'Confirmaciones procesadas correctamente',
            'total' => count($archivos)
        ], 200);
    }




}
