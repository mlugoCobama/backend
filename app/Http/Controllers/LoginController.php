<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Compras\Transformers\AuthResource;
use Illuminate\Support\Facades\DB;

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

        $user = new AuthResource(User::where('name', $fields['email'])
                    // ->where('activo', '1')
                    ->first());

        if (!$user || !( md5($fields['password']) === $user->password)) {
            if (!$user || !( sha1($fields['password']) === $user->password)) {
                if (!$user || !Hash::check($fields['password'],$user->password)) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Datos de usuario incorrectos' // include user role in response
                    ]);
                }
            }
        }

        $token = $user->createToken($fields['email'])->plainTextToken;

        $permisos = $this->getPermisos($user->id);

        return response()->json([
            'success' => true,
            'token' => $token,
            'role' => $user, // include user role in response
            'permisos' => $permisos
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
            ->get();

        return $permisos;

    }

}
