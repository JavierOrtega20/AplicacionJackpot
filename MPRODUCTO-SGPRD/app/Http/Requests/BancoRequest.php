<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BancoRequest extends FormRequest
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
            'descripcion'      => 'required|max:50',
            'telefono1'      => 'required|min:11|max:15',
            'rif'           => 'required|min:11',
            'contacto'      => 'required|min:8|max:50',
        ];
    }

    public function messages()
    {
     return [
            'descripcion.required' => 'El campo Nombre es obligatorio',
            'descripcion.max' => 'El campo Nombre debe tener maximo 50 digitos',
            'telefono1.required' => 'El campo Teléfono 1 es obligatorio',
            'telefono1.min' => 'El Teléfono 1 debe tener al menos 11 dígitos',
            'telefono1.max' => 'El Teléfono 1 debe tener maximo 15 dígitos',
            'rif.required' => 'El campo Rif es obligatorio',
            'rif.min' => 'El Rif debe tener al menos 11 dígitos',
            'contacto.required' => 'El campo Contacto es obligatorio',
            'contacto.min' => 'El campo Contacto debe tener al menos 8 digitos',
            'contacto.max' => 'El campo Contacto debe tener maximo 50 digitos',

        ];
    }
}
