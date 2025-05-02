<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Transformers\usersResource;
use Illuminate\Support\Facades\File;

class UsuariosController extends Controller
{
    /** ******************************************************************
     * Recupera el catalogo de empresa y num_intercompania
     *******************************************************************/
    public function index()
    {
        $data = DB::connection('intranet')->table('glpi_entities')->select('name','intercompania')->where('intercompania', '>', '0')->get();

        $interAgencias = array_flip([7102, 7075, 7074, 7072, 7071, 7064, 7063, 7062, 7061, 7051, 712, 710, 706]);

        foreach ($data as $item) {
            $item->isAgencia = isset($interAgencias[$item->intercompania]);   
        }

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
        // $data = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $id . ')');
        $content = File::get(base_path('\Modules\Compras\resources\assets\json\centrosCostos\catCentrosCostos.json'));
        // $rawCentrosCosto = (__DIR__ . "/../../../resources/assets/json/caCentrosCostos.json");
        $jsonCC = json_decode(json: $content, associative: true);
        $dataCC = $jsonCC;

        return response()->json([
            'status' => 'success',
            // 'data' => usersResource::collection($data),
            'data' => $jsonCC[1]['descripcion'],
            'message' => 'Datos recuperados correctamente'
        ]);
    }

    /** ***********************************************************
     * Recupera un usuario especifico DEL INTRANET por su correo
     ************************************************************/
    public function getDataUsuario($correo)
    {
        $data = DB::connection('intranet')->table('glpi_users')->select('*')->where('name', $correo)->first();
        
        if(empty($data)){
             return response()->json([
                 'status' => 'error',
                 'message' => 'El usuario no existe'
             ]);
         }

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
