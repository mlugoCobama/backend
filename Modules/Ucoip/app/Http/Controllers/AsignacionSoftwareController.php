<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Ucoip\Models\Software;
use Modules\Ucoip\Models\SoftwareUcoip;
use App\Enums\EstatusActivos;
use App\Enums\EstatusAsignaciones;

class AsignacionSoftwareController extends Controller
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
        $data = $request->all();
        $asignacion = new SoftwareUcoip();
        $asignacion->ucoip_ucoip_id = $data['idUcoip'];
        $asignacion->ucoip_software_id = $data['software']; 
        $asignacion->fecha_asignacion = now();
        $asignacion->save();

        $this->updateStatusSoftware($data['software'], EstatusActivos::ASIGNADA);

        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => "Asignación realizada correctamente"
        ]);
    
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $asiganciones = SoftwareUcoip::with(['licencia.tipoSoftware'])->where('ucoip_ucoip_id', $id)->get();

        return response()->json([
            'message' => 'asignaciones recuperadas correctamente',
            'data' => $asiganciones,
            'status' => 'success'
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
       $asignacion = SoftwareUcoip::find($id);
       if($asignacion){
        $asignacion->fecha_retiro = now();
        $asignacion->activo = EstatusAsignaciones::FINALIZADA;
        $asignacion->save();
        $this->updateStatusSoftware($asignacion->ucoip_software_id, EstatusActivos::DISPONIBLE);
       }

        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => "Operación realizada correctamente"
        ]);

      
    }

    public function updateStatusSoftware($id, $status){
        $software = Software::find($id);

        if($software){
            $software->estatus = $status;
            $software->save();
        }
    }
}
