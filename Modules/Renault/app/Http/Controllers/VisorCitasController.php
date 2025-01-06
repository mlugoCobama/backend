<?php

namespace Modules\Renault\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Modules\Renault\Transformers\CitasServicioResource;

use Modules\Renault\Models\RenCitasServicio;
use Modules\Renault\Models\RenEntradaVehiculo;
use Modules\Renault\Models\RenInventarioVehiculo;
use Modules\Renault\Models\RenTestigosFotograficos;

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
                $citaCita->agencia_id = 3;
                $citaCita->save();
            }
        }
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

        /**
         * Insertamos la entrada
         */
        $entrada = RenEntradaVehiculo::create([
            "fecha" => date('Y-m-d H:i:s'),
            "folio" => $request->form['num_entrada'],
            "num_entrada" => $request->form['num_entrada'],
            "ren_citas_servicio_id" => $request->form['citas_servicio_id'],
        ]);
        /**
         * Insertamos el inventario del vehiculo
         */
        RenInventarioVehiculo::create([
            'antena' => $request->form['antena'],
            'espejo' => $request->form['espejo'],
            'tapones' => $request->form['tapones'],
            'rines' => $request->form['rines'],
            'tapon_gasolina' => $request->form['tapon_gasolina'],
            'radio' => $request->form['radio'],
            'encendedor' => $request->form['encendedor'],
            'tapetes' => $request->form['tapetes'],
            'llanta_refaccion' => $request->form['llanta_refaccion'],
            'herramientas' => $request->form['herramientas'],
            'reflejantes' => $request->form['reflejantes'],
            'extinguidor' => $request->form['extinguidor'],
            'cables_corriente' => $request->form['cables_corriente'],
            'gato' => $request->form['gato'],
            'objetos_valor' => $request->form['objetos_valor'],
            'otros' => $request->form['otros'],
            'vestiduras' => $request->form['vestiduras'],
            'cristales' => $request->form['cristales'],
            'ren_entrada_vehiculo_id' => $entrada->id
        ]);

        foreach( $request->fotos as $foto) {

            $image = $foto['webviewPath'];  // your base64 encoded
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            Storage::disk('local')->put("renault/citas_servicio/".$foto['filepath'], base64_decode($image));
            /**
             * Insertamos los testigos fotograficos
             */
            RenTestigosFotograficos::create([
                "folio" => $request->form['folio'],
                "ruta" => "renault/citas_servicio/",
                "nombre" => $foto['filepath'],
                'ren_entrada_vehiculo_id' => $entrada->id
            ]);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {

        $date = date('Y-m-d');

        $citas = RenCitasServicio::where('agencia_id', $id)->where('fecha', 'like', $date."%" )->get();

        return response()->json([
            'success' => true,
            'message' => '',
            'data' =>  CitasServicioResource::collection($citas)
        ]);
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
