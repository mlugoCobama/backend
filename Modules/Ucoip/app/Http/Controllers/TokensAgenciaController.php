<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Enums\EstatusActivos;
use App\Enums\EstatusAsignaciones;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Ucoip\Models\CatEmpresas;
use Modules\Ucoip\Models\CatPuestosMarca;
use Modules\Ucoip\Models\TokenAgencia;

class TokensAgenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $tokens =  TokenAgencia::with(['puestoMarca','sucursal'])->get();
        $catalogo =  CatPuestosMarca::get();
        $empresas = CatEmpresas::where('division', 4)->get();

        $data = [
            'tokens' => $tokens,
            'puestos' => $catalogo ?? [],
            'sucursales' => $empresas ?? [] 
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Datso recuperados correctamente'
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
    public function store(Request $request)
    {
        if (!empty($request->id) && $tokenAgencia = TokenAgencia::find($request->id)) {

            $message = 'Registro actualizado con éxito';

        } else {
            // VALIDAR QUE EL TOKEN NO ESTE REGISTRADO ANTERIORMENTE
            $existe = $tokenAgencia = TokenAgencia::where('token',$request->token)->first();

            //Si YA EXISTE NOTIFICAR
            if($existe){
                 return response()->json([
                    'status' => 'error',
                    'message' => 'Este token ya existe en otra sucursal',
                    'data' => []
                ]);
            }

            $tokenAgencia = new TokenAgencia();
            $tokenAgencia->activo = 1;

            $message = 'Registro creado con éxito';
        }

        $tokenAgencia->token = $request->token;
        $tokenAgencia->ucoip_puesto_marca_id = $request->puesto_marca;
        $tokenAgencia->ucoip_cat_empresas_id = $request->cat_empresas_id;
        $tokenAgencia->observaciones = $request->observaciones;
        $tokenAgencia->save();

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => []
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $data = TokenAgencia::where('estatus', EstatusActivos::DISPONIBLE)->where('ucoip_cat_empresas_id', $id)->get();
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Hardware disponible recuperado correctamente'
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
