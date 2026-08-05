<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Ucoip\Models\HardwarePcModel;
use Modules\Ucoip\Transformers\HardwareResource;

class HardwareInfraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $data = HardwarePcModel::active()
            ->infraestructura()
            ->with([
                'Tipo',
                'empresa',
                'asignacion.userGlpi',
                'cambiosHardware.detalle',
                'intercambios.origen',
                'intercambios.destino',
                'mantenimientos' => function ($query) {
                        $query->select('id', 'ucoip_hardware_id', 'tipo', 'fecha', 'id_tecnico')->orderByDesc('fecha');
                    },
                'mantenimientos.tecnico:id,firstname,realname',
            ])
            ->get();
        // , 'cambiosHardware.detalle.cotizacionSeleccionada'
        return response()->json([
            'success' => true,
            'message' => '',
            'data' => HardwareResource::collection($data)
        ]);
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
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('ucoip::show');
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
