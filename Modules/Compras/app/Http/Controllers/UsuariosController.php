<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class UsuariosController extends Controller
{
    /** ******************************************************************
     * Recupera el catalogo de empresa y num_intercompania
     *******************************************************************/
    public function index()
    {
        $data = DB::connection('intranet')->table('glpi_entities')->select('name','intercompania')->where('intercompania', '>', '0')->get();

        return response()->json([
            'status' => 'success',
            'data' =>  $data,
            'message' => 'Datos recuperados correctamente'
        ]);
    }


    public function create()
    {
        return view('compras::create');
    }


    public function store(Request $request)
    {
        //
    }

    /** ***********************************************************
     * Recupera un usuario DEL INTRANET especifico por su id
     ************************************************************/
    public function show($id)
    {
        $data = DB::connection('intranet')->select('call SOPORTEZM.SP_GetDataUsuarios(' . $id . ')');

        return response()->json([
            'status' => 'success',
            'data' =>  $data,
            'message' => 'Datos recuperados correctamente'
        ]);
    }

    /** ***********************************************************
     * Recupera un usuario especifico DEL INTRANET por su correo
     ************************************************************/
    public function getDataUsuario($correo)
    {
        $data = DB::connection('intranet')->table('glpi_users')->select('*')->where('name', $correo)->get();
        return response()->json([
            'status' => 'success',
            'data' =>  $data,
            'message' => 'Datos recuperados correctamente'
        ]);
    }

    public function edit($id)
    {
        return view('compras::edit');
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
