<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            //Valida el registro
            $data = $request->validated();

            //Autenticar al usuario
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'role_id' => $data['role_id'],
                
            ]);
            $token = $user->createToken('token')->plainTextToken;
            
            return [
                'token' => $token,
                'user' => $user,
            ];
        } catch (\Exception $e) {
            return response([
                'errors' => ['Ocurrio algo inesperado con el servidor']
            ], 422);
        }
    }
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        // Revisar el Password
        if (!Auth::attempt($data)) {
            return response([
                'errors' => ['El correo o la contraseña son incorrectos']
            ], 422);
        }

        //Autenticar al usuario
        $user = Auth::user();
        $user2 = User::with('role')->find($user->id);

        $token = $user->createToken('token')->plainTextToken;


        return [
            'token' => $token,
            'user' => $user2,

        ];
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return [
            'user' => null
        ];
    }
}
