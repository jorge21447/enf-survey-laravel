<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\confirm;
use App\Http\Resources\UserCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password as PasswordRules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return new UserCollection(User::with('role')->paginate());

        return new UserCollection(User::with('role')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => ['required', 'string'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => [
                    'required',
                    PasswordRules::min(8)->letters()->symbols()->numbers()
                ],
                'role_id' => ['required', 'exists:roles,id'],
                'photo_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ],
            [
                'name.required' => 'El nombre es obligatorio',
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El email no tiene un formato válido',
                'email.unique' => 'El email ya se encuentra registrado',
                'password' => 'La contraseña debe contener al menos 8 caracteres, un símbolo y un número',
                'role_id.required' => 'El rol es obligatorio',
                'role_id.exists' => 'El Rol ID no es válido',
                'photo_profile.image' => 'La foto del perfil debe ser una imagen. ',
                'photo_profile.mimes' => 'La foto del perfil debe ser un archivo de tipo: JPEG, PNG, JPG, GIF. ',
                'photo_profile.max' => 'El archivo no debe superar los 2048 kB.'
            ]
        );


        try {

            $data = $request->all();

            
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'role_id' => $data['role_id'],
                'photo_profile' => $data['photo_profile'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            if ($request->hasFile('photo_profile')) {

                $file = $request->file('photo_profile');
                $nameProfile = $file->hashName(); // Generate a unique, random name...
                $extension = $file->extension();
                $photoProfilePath = "public/images/profiles/$nameProfile"; // Customized path
                $file->storeAs('public/images/profiles', "$nameProfile");
                $user->photo_profile = $photoProfilePath;
            };

            $user->save();
            return response([
                'message' => ['Usuario creado correctamente']
            ], 201);
        } catch (\Exception $e) {
            //Obtener el error
            return response(['errors' => ['Ocurrio algo inesperado con el servidor', $e->getMessage()]], 422);
            return response([
                'errors' => ['Ocurrio algo inesperado con el servidor']
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response([
                'message' => ['Usuario no encontrado']
            ], 404);
        }
        return response([
            'user' => $user
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
        $user = User::find($id);
        if (!$user) {
            return response([
                'message' => ['Usuario no encontrado']
            ], 404);
        }

        $data = $request->validate(
            [
                'name' => ['required', 'string'],
                'email' => ['required', 'email', 'unique:users,email,' . $id],
                'role_id' => ['required', 'exists:roles,id'],
                'password' => ['nullable', 'confirmed', PasswordRules::min(8)->letters()->symbols()->numbers()],
                'photo_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ],
            [
                'name.required' => 'El nombre es obligatorio',
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El email no tiene un formato válido',
                'email.unique' => 'El email ya se encuentra registrado',
                'role_id.required' => 'El rol es obligatorio',
                'role_id.exists' => 'El Rol ID no es válido',
                'password' => 'La contraseña debe contener al menos 8 caracteres, un símbolo y un número',
                'password.confirmed' => 'Las contraseñas deben coincidir.',
                'photo_profile.image' => 'La foto del perfil debe ser una imagen. ',
                'photo_profile.mimes' => 'La foto del perfil debe ser un archivo de tipo: JPEG, PNG, JPG, GIF. ',
                'photo_profile.max' => 'El archivo no debe superar los 2048 kB. '
            ]
        );


        try {
            // Solo si el Request solicita Password
            //$user->password = Hash::make($request->password);

            $user->name = $request->name;
            $user->email = $request->email;
            $user->role_id = $request->role_id;

            
            // Verificar si 'is_active' está presente en la solicitud

            $user->date_of_birth = $request->date_of_birth ?? null;
            $user->is_active = $request->is_active ?? true;


            //$user->photo_profile = $request->photo_profile ?? null;
            $oldPhotoPath = $user->photo_profile;

            if ($request->hasFile('photo_profile')) {

                if ($oldPhotoPath && Storage::exists($oldPhotoPath)) {
                    Storage::delete($oldPhotoPath);
                }

                $file = $request->file('photo_profile');
                $nameProfile = $file->hashName(); // Generate a unique, random name...
                $extension = $file->extension();
                $photoProfilePath = "public/images/profiles/$nameProfile"; // Customized path
                $file->storeAs('public/images/profiles', "$nameProfile");
                $user->photo_profile = $photoProfilePath;
            };


            if ($request->password) {
                $user->password = Hash::make($request->password);
            };


            $user->save();

            DB::commit();
            return response([
                'message' => ['Usuario actualizado correctamente']
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();
            return response([
                'errors' => ['Ocurrio algo inesperado con el servidor', $e->getMessage()]
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response([
                'message' => ['Usuario no encontrado']
            ], 404);
        }
        $oldPhotoPath = $user->photo_profile;
        if ($oldPhotoPath && Storage::exists($oldPhotoPath)) {
            Storage::delete($oldPhotoPath);
        }
        $user->delete();
        return response([
            'message' =>  ['Usuario eliminado correctamente']
        ], 200);
    }
}
