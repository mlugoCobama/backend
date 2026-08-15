<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Compras\Transformers\AuthResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Http\Controllers\UsuariosController;
use Modules\Compras\Transformers\UsersResource;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('name', $fields['email'])->first();

         if (!$user || !( md5($fields['password']) === $user->password)) {
             if (!$user || !( sha1($fields['password']) === $user->password)) {
                 if (!$user || !Hash::check($fields['password'],$user->password)) {
                     return response()->json([
                         'success' => false,
                         'error' => 'Datos de usuario incorrectos' //   include user role in response
                     ]);
                 }
             }
         }

        $token = $user->createToken($fields['email'])->plainTextToken;
        $permisos = $this->getPermisos($user->id);
        $usuarioActivo = $this->getUsuarioActivo($fields['email']);

        return response()->json([
            'success' => true,
            'token' => $token,
            'role' => new AuthResource($user), // include user role in response
            'permisos' => $permisos,
            'ip' => $request->ip(),
            'usuarioActivo' => $usuarioActivo,
        ]);
    }

    public function loginValidate() {
        return response()->json([
           'success' => false,
           'message' => 'Iniciar sesion nuevante',
        ]);
    }

    private function getPermisos($id) {

        $permisos = DB::table('permissions as p')
            ->join('model_has_permissions as mp', 'p.id', '=', 'mp.permission_id')
            ->select('p.name', 'p.guard_name')
            ->where('model_id', '=', $id)
            // ->get()
            ;

        $permisos_as =
            DB::table('permissions as p')
                ->join('permission_has_puesto as pp', 'p.id', '=', 'pp.permiso_id')
                ->join('cap_cataologo_puestos as cp', function ($join) {
                    $join->on('cp.id', '=', 'pp.puesto_id')
                        ->where('cp.activo', '=', 1);
                })
                ->join('usuario_puesto as up', function ($join) {
                    $join->on('cp.id', '=', 'up.id_puesto')
                        ->where('up.activo', '=', 1);
                })
                ->where('up.id_usuario', '=', $id)
                ->select('p.name', 'p.guard_name')
            ;

        $union = $permisos->union($permisos_as)->get();
        return $union;

    }

    private function getUsuarioActivo($correo){
        $data = DB::connection('intranet')->select("call SOPORTEZM.SP_GetUsuarioEmail('$correo')");
        return  UsersResource::collection($data);
    }

    public function loginMobile(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('name', $fields['email'])->first();

        $validPassword =
            $user &&
            (
                md5($fields['password']) === $user->password ||
                sha1($fields['password']) === $user->password ||
                Hash::check($fields['password'], $user->password)
            );

        if (!$validPassword) {
            return response()->json([
                'success' => false,
                'error' => 'Credenciales incorrectas'
            ], 401);
        }

        $token = $user->createToken($fields['email'], ['mobile'])->plainTextToken;
        $usuarioActivo = DB::connection('intranet')->select("call SOPORTEZM.SP_GetUsuarioEmail(?)", [$fields['email']]);
        $permisos = $this->getPermisosMovil($user->id);
        return response()->json([
            'success' => true,
            'token' => $token,
            'role' => new AuthResource($user),
            'ip' => $request->ip(),
            'usuarioActivo' => $usuarioActivo,
            "permissions" => $permisos
        ]);
    }

    private function getPermisosMovil($id)
    {
        return DB::table('permissions as p')
            ->join('model_has_permissions as mp', 'p.id', '=', 'mp.permission_id')
            ->where('p.sistema', '=', 4)
            ->where('mp.model_id', '=', $id)
            ->pluck('p.name');
    }

}
