<?php

namespace Modules\Renault\Http\Controllers;

use App\Http\Controllers\Controller;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Renault\Models\RenCitasServicio;
use Modules\Renault\Models\RenEventosCita;

class EventosCitaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('renault::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('renault::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $evento =  new RenEventosCita();
       $evento->ren_cat_eventos_id = $request->tipo_evento;
       $evento->ren_citas_servicio_id = $request->cita_id;
       $evento->inicio_evento = now();
       $evento->save();

       $this->updateEstatus($request->cita_id, $request->tipo_evento);


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
        return view('renault::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('renault::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
       $evento =  RenEventosCita::find($id);
       $evento->fin_evento = now();
       $evento->observaciones = $request->observaciones;
       $evento->save();

        if($evento->ren_cat_eventos_id == 4){
            $this->updateEstatus($evento->ren_citas_servicio_id, 5);
        }

        if($evento->ren_cat_eventos_id == 6){
            $this->updateEstatus($evento->ren_citas_servicio_id, 7);
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

    public function updateEstatus($idCita, $estatus){
        $estatusTexto = match ($estatus) {
            1 => 'AC',
            2 => 'AT',
            3 => 'AL',
            4 => 'CA',
            5 => 'TE',
            6 => 'EN',
            7 => 'FN',
            default => $estatus
        };
         RenCitasServicio::where('id', $idCita)->update([
                'estatus' => $estatusTexto,
                ]);
    }
}
