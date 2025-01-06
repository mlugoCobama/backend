<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        $user = User::where('email', $fields['email'])
                    ->where('activo', '1')
                    ->first();

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

        return response()->json([
            'success' => true,
            'token' => $token,
            'Type' => 'Bearer',
            'role' => $user // include user role in response
        ]);
    }

    public function loginValidate() {
        return response()->json([
           'success' => false,
           'message' => 'Iniciar sesion nuevante',
        ]);
    }

}
