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
        // $idsAExcluir = [7074, 7072, 7075, 7102];

        // $data = DB::connection('intranet')
        // ->table('glpi_entities')
        // ->select('name','intercompania')
        // ->where('intercompania', '>', '0')
        // ->whereNotIn('intercompania', $idsAExcluir)
        // ->orderBy('name')->get();
 
        $data = DB::connection('intranet')->select('CALL SP_GetEmpresas()');

        // $dataArray = $data->toArray();

        // $dataArray[] = (object)[
        //     'name' => 'MACRO TALLER',
        //     'intercompania' => 119,
        //     'isAgencia' => false
        // ];
        // $dataArray[] = (object)[
        //     'name' => 'Flamamex - Flamazul',
        //     'intercompania' => 250,
        //     'isAgencia' => false
        // ];
        // $dataArray[] = (object)[
        //     'name' => 'Garza Sur - Urbano',
        //     'intercompania' => 111,
        //     'isAgencia' => false
        // ];

        return response()->json([
            'status' => 'success',
            'data' =>  CatEmpresasResource::collection($data),
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

        $interExcepciones = explode(',', env('INTER_EXECPCIONES')); 
        $isExcepcion = in_array($id, $interExcepciones );

        if($isExcepcion){
            $idUser = auth()->id(); 
            $data = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $idUser . ')');
            $id = $data[0]->intercompania;
        }

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

    function asignarPermisosPorTipo($id, string $tipo)
    {
        // Definimos los arrays de permisos por tipo
        $permisosAdmin   = [255, 254, 253,252,251,250,249,248,247,246,245,244,243,242];
        $permisosCompras = [255, 254, 253,252,251,250,249,248,247,246,245,244,243];
        $permisosAdminz  = [253,252,251,250,249,248,247];
        $permisosEmpresas= [255, 254, 253,252,251, 249,248,243,242];
        $permisosMacro= [255,254, 253,252,251,250,249,248,247,246,245,244,243,242];


        // Diccionario de referencia
        $rolesPermisos = [
            'admin'    => $permisosAdmin,
            'compras'  => $permisosCompras,
            'adminz'   => $permisosAdminz,
            'empresas' => $permisosEmpresas,
            'macro' => $permisosMacro,
        ];

        // Validamos que el tipo exista
        if (!array_key_exists($tipo, $rolesPermisos)) {
            throw new \Exception("El tipo {$tipo} no está definido en los permisos.");
        }

        // Construimos el bloque de inserción
        $data = [];
        foreach ($rolesPermisos[$tipo] as $permisoId) {
             $existe = DB::table('model_has_permissions')
            ->where('permission_id', $permisoId)
            ->where('model_id', $id)
            ->where('model_type', 'App\\Models\\User')
            ->exists();
            if(!$existe){
                $data[] = [
                'permission_id' => $permisoId,
                'model_type'    => 'App\\Models\\User', // Ajusta según tu modelo
                'model_id'      => $id,
            ];
            }
        }

        // Insertamos todos los permisos de golpe
        DB::table('model_has_permissions')->insert($data);
    }

    public function asignar(Request $request)
    {
        $id   = $request->input('id');
        $tipo = $request->input('tipo'); // admin, compras, adminz, empresas

        $this->asignarPermisosPorTipo($id, $tipo);

        return response()->json([
            'message' => "Permisos asignados correctamente al usuario {$id} con tipo {$tipo}"
        ]);
    }




}
