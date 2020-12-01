<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MonedasRequest extends FormRequest
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
            'divisa'      => 'required|min:1|max:20',
            'simbolo'     => 'required|min:1|max:7',
            'descripcion' => 'required|min:1|max:50',

            'mon_nombre'      => 'sometimes|required|min:1|max:20',
            'mon_simbolo'     => 'sometimes|required|min:1|max:7',
            'mon_observaciones' => 'sometimes|required|min:1|max:50',
        ];
    }

    public function messages()
    {
     return [
            'mon_nombre.required'       => 'El campo Divisas es obligatorio',
            'mon_simbolo.required'      => 'El campo Símbolo es obligatorio',
            'mon_observaciones.required'  => 'El campo Descripción es obligatorio',

            'divisa.required'       => 'El campo Divisas es obligatorio',
            'simbolo.required'      => 'El campo Símbolo es obligatorio',
            'descripcion.required'  => 'El campo Descripción es obligatorio',
        ];
    }
}
