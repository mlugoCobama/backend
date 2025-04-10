<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class UsuariosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = DB::connection('intranet')->table('glpi_entities')->select('name','intercompania')->where('intercompania', '>', '0')->get();

        return response()->json([
            'status' => 'success',
            'data' =>  $data,
            'message' => 'Datos recuperados correctamente'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('compras::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $data = DB::connection('intranet')->select('call SOPORTEZM.SP_GetDataUsuarios(' . $id . ')');

        return response()->json([
            'status' => 'success',
            'data' =>  $data,
            'message' => 'Datos recuperados correctamente'
        ]);
    }

    public function getDataUsuario($correo)
    {
        $data = DB::connection('intranet')->table('glpi_users')->select('*')->where('name', $correo)->get();
        return response()->json([
            'status' => 'success',
            'data' =>  $data,
            'message' => 'Datos recuperados correctamente'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('compras::edit');
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
