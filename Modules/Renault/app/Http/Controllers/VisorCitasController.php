<?php

namespace Modules\Renault\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use Modules\Renault\Transformers\CitasServicioResource;

use Modules\Renault\Models\RenCitasServicio;

class VisorCitasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $date = date('Ymd');

        $citas = DB::connection('renault')
                    ->table('Se_Citas')
                    ->select(
                        'Se_Citas.citas_folio',
                        'Se_Citas.citas_empl_clave',
                        'empleados.empl_nombre',
                        'Se_Citas.citas_fechacita',
                        'Se_Citas.citas_nombre',
                        'Se_Citas.citas_apaterno',
                        'Se_Citas.citas_amaterno',
                        'Se_Citas.citas_modelo',
                        'Se_Citas.citas_tipo',
                        'Se_Citas.citas_placas',
                        'Se_Citas.citas_observaciones',
                        'Se_Citas.citas_Color1',
                        'Se_Citas.citas_AnioModelo',
                        'Se_Citas.citas_status',
                        'Se_Citas.citas_TipoCita',
                        'Se_Citas.citas_NoSerie',
                        'Se_Citas.citas_TelefonoContacto',
                        'Se_Citas.citas_Domicilio',
                        'Se_Citas.citas_Kilometraje',
                        'Se_Citas.citas_email',
                        'Se_Citas.citas_RFC',
                        )
                    ->join('empleados', 'Se_Citas.citas_empl_clave', '=', 'empleados.empl_clave')
                    ->where('Se_Citas.citas_idagencia', '=',3)
                    ->where('Se_Citas.citas_status', '<>', 'BO')
                    ->whereBetween('Se_Citas.citas_fechacita', [$date.' 00:00:00.000',$date.' 23:59:59.997'])
                    ->orderBy('Se_Citas.citas_fechacita', 'asc')
                    ->orderBy('Se_Citas.citas_empl_clave', 'asc')
                    ->get();

        for ($i = 0; $i < count($citas); $i++) {

            $existe = RenCitasServicio::where('folio', $citas[$i]->citas_folio)->get();

            if ($existe->count() == 0) {
                $citaCita = new RenCitasServicio();
                $citaCita->folio = $citas[$i]->citas_folio;
                $citaCita->empleado_id = $citas[$i]->citas_empl_clave;
                $citaCita->fecha = $citas[$i]->citas_fechacita;
                $citaCita->nombre = $citas[$i]->citas_nombre;
                $citaCita->apellido_paterno = $citas[$i]->citas_apaterno;
                $citaCita->apellido_materno = $citas[$i]->citas_amaterno;
                $citaCita->rfc = $citas[$i]->citas_RFC;
                $citaCita->telefono = $citas[$i]->citas_TelefonoContacto;
                $citaCita->domicilio = $citas[$i]->citas_Domicilio;
                $citaCita->email = $citas[$i]->citas_email;
                $citaCita->vin = $citas[$i]->citas_NoSerie;
                $citaCita->modelo = $citas[$i]->citas_modelo;
                $citaCita->placas = $citas[$i]->citas_placas;
                $citaCita->color = $citas[$i]->citas_Color1;
                $citaCita->tipo = $citas[$i]->citas_tipo;
                $citaCita->anio = $citas[$i]->citas_AnioModelo;
                $citaCita->kilometraje = $citas[$i]->citas_Kilometraje;
                $citaCita->observaciones = $citas[$i]->citas_observaciones;
                $citaCita->tipo_cita = $citas[$i]->citas_TipoCita;
                $citaCita->estatus = $citas[$i]->citas_status;
                $citaCita->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => '',
            'data' =>  CitasServicioResource::collection($citas)
        ]);
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
        return "<img src='blob:http://localhost:8100/0df8dce1-6b74-43ae-8202-3c553e89bc58'>";
        return $request->fotos;
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
