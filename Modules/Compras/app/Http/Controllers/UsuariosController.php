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
 
        $data = DB::connection('intranet')->select('CALL SP_GetEmpresas()');

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

        // if($isExcepcion){
        //     $idUser = auth()->id(); 
        //     $data = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $idUser . ')');
        //     $id = $data[0]->intercompania;
        // }

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
                'model_type'    => 'App\\Models\\User', 
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

    public function getAllPermission(Request $request){
        $data = $request->all();
        $correoUsuaio = $data['correo'] ?? null;

        $idUsuario = null;
        if ($correoUsuaio) {
            $idUsuario = DB::connection('intranet')
                ->table('glpi_users')
                ->where('name', $correoUsuaio)
                ->value('id');
        }

        // Consulta base: todos los permisos de todos los módulos
        $query = DB::table('ucoip_modulos as um')
            ->join('permissions as p', 'p.ucoip_modulo_id', '=', 'um.id')
            ->select(
                'um.id as modulo_id',
                'um.nombre as modulo',
                'p.id as permiso_id',
                'p.name as permiso',
                'p.sistema',
                'p.descripcion'
            )
            ->where('p.sistema', '<>', 2)
            // ->orderBy('um.nombre', 'asc')
            // ->orderBy('p.descripcion', 'asc')
            ;

        // Si hay usuario, hacemos LEFT JOIN para marcar permisos activos
        if ($idUsuario) {
            $query->leftJoin('model_has_permissions as mhp', function($join) use ($idUsuario) {
                $join->on('mhp.permission_id', '=', 'p.id')
                    ->where('mhp.model_id', '=', $idUsuario);
            })
            ->addSelect('mhp.model_id as usuario_id');
        }

        $rows = $query->get();

        // Agrupar por módulo y anidar permisos
        $modulos = $rows->groupBy('modulo_id')->map(function ($items) use ($idUsuario) {
            $modulo = $items->first();
            return [
                'id' => $modulo->modulo_id,
                'nombre' => $modulo->modulo,
                'permisos' => $items->map(function ($permiso) use ($idUsuario) {
                    return [
                        'id' => $permiso->permiso_id,
                        'nombre' => $permiso->permiso,
                        'descripcion' => $permiso->descripcion,
                        // Si no hay usuario, todos vienen como false
                        'activo' => $idUsuario ? ($permiso->usuario_id ? true : false) : false,
                    ];
                })->values()
            ];
        })->values();

        $data = [
            'usuario' => $idUsuario ? ($correoUsuaio ?? 'Todos') : 'El usuario No Existe',
            'intranet' => $idUsuario ?? 'No filtrado',
            'permisos' => $modulos
        ];

        return response()->json([
            'data' => $data
        ]);

        
    }

    public function saveOrUpdatePermissions(Request $request)
    {
        $usuarioId = $request->input('usuario_id');
        $permisosSeleccionados = $request->input('permisos', []); // array de IDs de permisos

        if (!$usuarioId) {
            return response()->json([
                'message' => 'Usuario no válido'
            ], 400);
        }

        // Traer permisos actuales del usuario
        $permisosActuales = DB::table('model_has_permissions')
            ->where('model_id', $usuarioId)
            ->pluck('permission_id')
            ->toArray();

        // Determinar cuáles agregar y cuáles eliminar
        $agregar = array_diff($permisosSeleccionados, $permisosActuales);
        $eliminar = array_diff($permisosActuales, $permisosSeleccionados);

        // Eliminar los permisos desmarcados
        if (!empty($eliminar)) {
            DB::table('model_has_permissions')
                ->where('model_id', $usuarioId)
                ->whereIn('permission_id', $eliminar)
                ->delete();
        }

        // Insertar los permisos nuevos
        $dataInsert = collect($agregar)->map(function ($permisoId) use ($usuarioId) {
            return [
                'permission_id' => $permisoId,
                'model_type' => 'App\\Models\\User', 
                'model_id' => $usuarioId,
            ];
        })->toArray();

        if (!empty($dataInsert)) {
            DB::table('model_has_permissions')->insert($dataInsert);
        }

        return response()->json([
            'message' => 'Permisos sincronizados correctamente',
            'usuario_id' => $usuarioId,
            'permisos' => $permisosSeleccionados
        ]);
    }

    public function getTecnicos(){
        $data = DB::connection('intranet')->select("call SOPORTEZM.SP_GetTecnicosTi()");
        
        if(empty($data)){
             return response()->json([
                 'status' => 'error',
                 'message' => 'No hay técnicos disponibles'
             ]);
         }

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Datos recuperados correctamente'
        ]);
    }





}
