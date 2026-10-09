<?php

namespace Modules\ParqueVehicular\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\ParqueVehicular\Services\EventosOsService;
use Modules\ParqueVehicular\Models\EventosProvOrden;
use Modules\ParqueVehicular\Models\OrdenProveedor;
use Modules\ParqueVehicular\Models\OrdenServicio;
use Modules\ParqueVehicular\Services\OrdenServicioCobService;
use Modules\Renault\Models\RenCitasServicio;

class EventosOrdenController extends Controller
{

    public function __construct(
        protected EventosOsService $eventosService,
        protected OrdenServicioCobService $ordeServicioService) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('parquevehicular::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('parquevehicular::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $this->eventosService->finalizarUltimoEvento($request->cita_id);

        $evento = $this->eventosService->inciarEvento($request->cita_id, $request->tipo_evento);

        $this->ordeServicioService->updateEstatus($request->orden_servicio_id, $request->tipo_evento);

       return response()->json([
            'data' => $evento,
            'message' => 'Evento registrado correctamente',
            'status' => 'success',
       ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('parquevehicular::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('parquevehicular::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

    $evento = $this->eventosService->finalizarEvento($id, $request->observaciones);
        if($evento->ren_cat_eventos_id == 4){
            $this->ordeServicioService->updateEstatus($request->cita_id, 5);
        }

        if($evento->ren_cat_eventos_id == 6){
            $this->ordeServicioService->updateEstatus($request->cita_id, 7);
        }


       return response()->json([
            'data' => $evento,
            'message' => 'Evento actualizado correctamente correctamente',
            'status' => 'success',
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
