<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Ucoip\Models\RecursosRedUcoip;

class AsignacionRecursoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('ucoip::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('ucoip::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data =  $request->all();
        $asignacion                         =  new RecursosRedUcoip();
        $asignacion->equipo_id              =  $data['hardware'] ?? null;
        $asignacion->valor                  =  $data['valor'];
        $asignacion->nivel_restrictivo      =   $data['restrictivo'];
        $asignacion->observaciones          =  $data['observaciones'] ?? null;
        $asignacion->fecha_asignacion       =  now();
        $asignacion->ucoip_ucoip_id         =  $data['idUcoip'];
        $asignacion->ucoip_cat_recursos_id  =  $data['tipo'];

        $asignacion->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Sistema asignado correctamente',    
            'data' => []
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $recurso = RecursosRedUcoip::with(['recursoRed'])->where('ucoip_ucoip_id', $id)->get();

        return  response()->json([
            'status' => 'success',
            'data' => $recurso,
            'message' => 'Recursos recuperados correctamente'

        ]);

    
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('ucoip::edit');
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
}
