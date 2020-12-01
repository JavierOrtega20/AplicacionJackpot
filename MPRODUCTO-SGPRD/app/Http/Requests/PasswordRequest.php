<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //'password' => 'required|same:confirm-password|min:6|max:15',
            'confirm-password' => 'required|same:password|min:6|max:15',
        ];
    }

    public function messages()
    {
     return [
            'password.required' => 'El campo Contraseña es obligatorio.',
            //'password.same' => 'Los campos Contraseña y Confirmar Contraseña deben coincidir.',
            'confirm-password.same' => 'Los campos Contraseña y Confirmar Contraseña deben coincidir.',
            'password.min'=> 'El campo Contraseña debe tener al menos 6 carácteres',
            'password.max' => 'El campo Contraseña debe tener máximo 15 carácteres',
        ];
    }
}
