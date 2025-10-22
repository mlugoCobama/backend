<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Compras\Models\AcuseEntrega;
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\OrdenCompra;
use Modules\Compras\Models\SolicitudesCompra;

use App\Enums\EstatusOrdenCompra;
use App\Enums\EstatusSolicitud;

use Illuminate\Support\Facades\File;


class AcuseEntregaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $data = [];

        $solicitudesCompras =  SolicitudesCompra::get();

        foreach ($solicitudesCompras as $solicitud) {
            $detalles = $solicitud->DetallesSolicitud;
            $cotizaciones = $solicitud->Cotizaciones;

            $data["solicitud $solicitud->folio"] = $solicitud;
            $data["solicitud $solicitud->folio"]['detalles'] =  $detalles;
            $data["solicitud $solicitud->folio"]['cotización'] = $cotizaciones;
        }
        
        return response()->json([
            'data' => $data
        ]);

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'archivo' => 'required|file|mimes:pdf',
            'observaciones' => 'nullable|string',
            'orden_compra_id' => 'required|integer',
        ]);

        $ordenCompraId = $validated['orden_compra_id'];
        $fechaSubida = now()->format('Y-m-d'); 
        $nombreArchivo = "entrega_orden_{$ordenCompraId}_{$fechaSubida}." . $request->file('archivo')->getClientOriginalExtension();

        $carpeta = 'acuses/' . $ordenCompraId;
        $rutaArchivo = $request->file('archivo')->storeAs($carpeta, $nombreArchivo);


        $acuse = AcuseEntrega::create([
            'ruta' => $rutaArchivo,
            'comentario' => $validated['observaciones'],
            'fecha' => Carbon::now()->format('Y-m-d'),
            'orden_compra_id' => $validated['orden_compra_id'],
        ]);

        $this->actStatusOrdenSolicitud($ordenCompraId, EstatusOrdenCompra::ENTREGADA, EstatusSolicitud::ENTREGADA );
        return response()->json([
            'status' => 'success',
            'message' => 'Acuse de entrega creado correctamente',
            'data' => []
        ], 201);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('compras::show');
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

    public function actStatusOrdenSolicitud($idOrdenCompra, $statusOrdenCompra, $estatusSolicitud){

        $orden = OrdenCompra::where('id', $idOrdenCompra)->first();
        if ($orden) {
            $orden->estatus = $statusOrdenCompra;
            $orden->save(); 
        }

        $cotizacion = Cotizaciones::where('id', $orden->cotizaciones_id)->first();

        $solicitud = SolicitudesCompra::find($cotizacion->solicitudes_compra_id);
        if ($solicitud) {
            $solicitud->estatus = $estatusSolicitud;
            $solicitud->save(); 
        }

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
        $path = storage_path("app/acuses/$id/$file");
        if (!File::exists($path)) {
            abort(404);
        }
        $fileContent = File::get($path);
        $type = File::mimeType($path);
        return response($fileContent, 200)->header("Content-Type", $type);
    }
}
