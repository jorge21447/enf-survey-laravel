<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as PasswordRules;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                PasswordRules::min(8)->letters()->symbols()->numbers()
            ],
            'role_id' => ['required', 'exists:roles,id']
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email no tiene un formato válido',
            'email.unique' => 'El email ya se encuentra registrado',
            'password' => 'La contraseña debe contener al menos 8 caracteres, un símbolo y un número.',
            'password.required' => 'La contraseña es obligatoria',
            'password.confirmed' => 'Las contraseñas deben coincidir. ',
            'role_id.required' => 'El rol es obligatorio',
            'role_id.exists' => 'El Rol ID no es válido',
        ];
    }
}
