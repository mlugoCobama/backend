<?php

namespace App\Http\Controllers;

use App\Http\Resources\BalanzaResource;
use App\Models\Balanzas;
use App\Models\UsersGlpi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BalanzasController extends Controller
{
    private $balanza;

    public function __construct(Balanzas $balanza) {
        $this->balanza = $balanza;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usersGlpi = UsersGlpi::select(
                                        'name',
                                        'password',
                                        'realname',
                                        'firstname',
                                    )
                                ->where('is_active', '1')
                                ->get();

        foreach ($usersGlpi as $key) {

            if ( User::where('email', $key['name'])->doesntExist() ) {

                if ($key['password'] === null || $key['password'] === '') {
                    $key['password'] = Hash::make('C0b@mAU$3r');
                }
                $usersNew = new User;
                $usersNew->name = $key['firstname']." ".$key['realname'];
                $usersNew->email = $key['name'];
                $usersNew->password = $key['password'];
                $usersNew->activo = 1;
                $usersNew->tipo = 2;
                $usersNew->save();
            } else {
                $usersNew = User::where('email', '=', $key['name']);
                $usersNew->name = $key['firstname']." ".$key['realname'];
                $usersNew->password = $key['password'];
                $usersNew->activo = 1;
                $usersNew->tipo = 2;
                $usersNew->save();
            }
        }

        return $usersGlpi;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $empresa = $request->empresa;
        $anio = date('Y', strtotime($request->mes) );
        $mes = date('m', strtotime($request->mes) );
        $response = true;
        $message = 'Se ha guardado la información correctamente';

        if (
            $this->balanza
                ->where('cat_empresas_id', $empresa)
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->doesntExist()
            ) {

                foreach ($request->balanza as $key ) {
                    if( $key["cta"] != 0 ) {
                        $this->balanza->insert([
                            'cta' => $key["cta"] === null ? 0 : $key["cta"],
                            'scta' => $key["scta"] === null ? 0 : $key["scta"],
                            'sscta' => $key["sscta"] === null ? 0 : $key["sscta"],
                            'ssscta' => $key["ssscta"] === null ? 0 : $key["ssscta"],
                            'descripcion' => $key["descripcion"] === null ? 0 : $key["descripcion"],
                            'saldo_inicial' => $key["saldo_inicial"]  === null ? 0 : $key["saldo_inicial"],
                            'debe' => $key["debe"]  === null ? 0 : $key["debe"],
                            'haber' => $key["haber"]  === null ? 0 : $key["haber"],
                            'saldo_actual' => $key["saldo_actual"]  === null ? 0 : $key["saldo_actual"],
                            'mes' => $mes,
                            'anio' => $anio,
                            'cat_empresas_id' => $empresa,
                        ]);
                    }
                }

                DB::select('call SP_ingresos_gastos('.$mes.', '.$anio.', '.$empresa.');');
            } else {
                $response = false;
                $message = "Ya existe información captura de ese periodo";
            }


        //DB::select('call SP_ventas_autos('.$mes.', '.$anio.', '.$empresa.');');
        //DB::select('call SP_costos_autos('.$mes.', '.$anio.', '.$empresa.');');
        return response()->json([
            'success' => $response,
            'message' => $message,
            'data' => []
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $empresa_id, string $mes, string $anio)
    {
        $balanza =  BalanzaResource::collection( $this->balanza
                        ->where('cat_empresas_id', $empresa_id)
                        ->where('mes', $mes)
                        ->where('anio', $anio)
                        ->get() );

        if (count($balanza) > 0) {
            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $balanza
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No se tiene información captura.',
                'data' => []
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
