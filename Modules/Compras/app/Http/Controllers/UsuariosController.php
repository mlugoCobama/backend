<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Compras\Transformers\CatEmpresasResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Transformers\UsersResource;
class UsuariosController extends Controller
{
    /** ******************************************************************
     * Recupera el catalogo de empresa y num_intercompania
     *******************************************************************/
    public function index()
    {
         $idsAExcluir = [7074, 7072, 7075, 7102];

        $data = DB::connection('intranet')
        ->table('glpi_entities')
        ->select('name','intercompania')
        ->where('intercompania', '>', '0')
        ->whereNotIn('id', $idsAExcluir)
        ->orderBy('name')->get();
        

        $dataArray = $data->toArray();

        $dataArray[] = (object)[
            'name' => 'MACRO TALLER',
            'intercompania' => 119,
            'isAgencia' => false
            ];

        return response()->json([
            'status' => 'success',
            'data' =>  CatEmpresasResource::collection($dataArray),
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

    /** ***********************************************************
     * Recupera una colección de usuarios por su numero intercompania
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
     * Recupera un usuario DEL INTRANET especifico por su id
     ************************************************************/
    public function showById($id)
    {
        $data = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $id . ')');
        return response()->json([
            'status' => 'success',
            'data' => UsersResource::collection($data),
            'message' => 'Datos recuperados correctamente'
        ]);
    }

    /** ***********************************************************
     * Recupera un usuario especifico DEL INTRANET por su correo
     ************************************************************/
    public function getDataUsuario($correo)
    {
        $data = DB::connection('intranet')->select("call SOPORTEZM.SP_GetUsuarioEmail('$correo')");
        // $data = DB::connection('intranet')->table('glpi_users')->select('*')->where('name', $correo)->first();
        
        if(empty($data)){
             return response()->json([
                 'status' => 'error',
                 'message' => 'No se pudo validar el usuario'
             ]);
         }

        return response()->json([
            'status' => 'success',
            'data' => UsersResource::collection($data),
            'message' => 'Datos recuperados correctamente'
        ]);
    }
}
