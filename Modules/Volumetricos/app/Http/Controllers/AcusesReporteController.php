<?php

namespace Modules\Volumetricos\Http\Controllers;

use App\Http\Controllers\Controller;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Modules\Volumetricos\Models\AcusesReporte;
use Modules\Volumetricos\Models\ReporteVolumen;

class AcusesReporteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('volumetricos::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('volumetricos::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $archivo = $request->file('archivo');

        $nombreOriginal = $archivo->getClientOriginalName();

        $nombreArchivo = time() . '_' . $nombreOriginal;

        $rutaArchivo = $archivo->storeAs('volumenes/acusesreporte/'.$request->reporte_id , $nombreArchivo, 'public');

        $acuse = new AcusesReporte();
        $acuse->ruta = $rutaArchivo;
        $acuse->tipo = $request->tipo;
        $acuse->fecha = now();
        $acuse->vol_reporte_volumenes_id = $request->reporte_id;
        $acuse->save();

        $this->updateEstatusReporte($request->reporte_id, $request->tipo);

        return response()->json([
            'data' => [],
            'status' => 'success',
            'message' => 'Acuse guardado correctamente'
        ]);
    }

    public function updateEstatusReporte($idRepote, $tipo){
        $estatus = match($tipo){
            'entrega' => 2,
            'aceptacion' => 4,
            'rechazo' => 3,
            default => 1
        };

        $reporte = ReporteVolumen::find($idRepote);
        if($reporte){
            $reporte->estatus = $estatus;
            $reporte->save();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('volumetricos::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('volumetricos::edit');
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

    public function descargar($id)
    {
        $reporte = AcusesReporte::findOrFail($id);

        $disk = Storage::disk('public');

        if (!$disk->exists($reporte->ruta)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe en el servidor.'
            ], 404);
        }

        $ruta = $disk->path($reporte->ruta);

        $nombre = pathinfo($reporte->ruta,PATHINFO_BASENAME);

        return response()->download($ruta, $nombre);
    }
}
