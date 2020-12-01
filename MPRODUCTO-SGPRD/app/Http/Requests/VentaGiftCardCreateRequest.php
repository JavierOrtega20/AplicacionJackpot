<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VentaGiftCardCreateRequest extends FormRequest
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
       $validar_codigo=$this->cod_tel != "" ? ",". $this->cod_tel:",0";
	   $validar_numero=$this->num_tel != "" ? ",". $this->num_tel:",0";
	   
        return [
            'nacionalidad_comprador' => 'sometimes|required',
            'cedula_comprador' => 'sometimes|required|numeric|digits_between:3,10|unique:users,dni',
            'first_name_comprador' =>'sometimes|required|min:3|max:50',
            'last_name_comprador' => 'sometimes|required|min:3|max:50',
            'email_comprador' => 'sometimes|required|email|min:10|max:50|unique:users,email',
            'cod_tel_comprador' => 'sometimes|required|unique:users,cod_tel,NULL,cod_tel,num_tel'.$validar_numero,
            'num_tel_comprador' => 'sometimes|required|numeric|digits_between:7,7|unique:users,num_tel,NULL,num_tel,cod_tel'.$validar_codigo,
        ];
    }
	
    public function messages()
    {
     return [
            'nacionalidad_comprador.required' => 'El Campo Tipo de documento es obligatorio.',
            'cod_tel_comprador.required' => 'El campo código es obligatorio.',
            'cedula_comprador.required' => 'El campo Cédula es obligatorio.',
            'cedula_comprador.unique' => 'La Cédula ya se encuentra en nuestros registros.',
			'num_tel_comprador.unique' => 'El número de teléfono ya se encuentra en nuestros registros.',
			'cod_tel_comprador.unique' => 'El número de teléfono ya se encuentra en nuestros registros.',
            'cedula_comprador.numeric' => 'El campo Cédula debe ser un número.',
            'cedula_comprador.digits_between' => 'El campo Cédula debe contener entre 3 y 10 dígitos.',
            'first_name_comprador.required' => 'El campo Nombre es obligatorio.',
            'first_name_comprador.min'=> 'El campo Nombre debe tener al menos 3 carácteres.',
            'first_name_comprador.max' => 'El campo Nombre debe tener máximo 50 carácteres.',
            'last_name_comprador.required' => 'El campo Apellido es obligatorio.',
            'last_name_comprador.min'=> 'El campo Apellido debe tener al menos 3 carácteres.',
            'last_name_comprador.max' => 'El campo Apellido debe tener máximo 50 carácteres.',
            'email_comprador.required' => 'El campo Correo Electrónico es obligatorio.',
            'email_comprador.unique' => 'El Correo Electrónico que ha ingresado ya está en uso.',
            'email_comprador.email' => 'El campo Correo Electrónico debe ser una dirección de correo válida.',
            'email_comprador.min' => 'El Correo Electrónico debe tener al menos 10 carácteres.',
            'email_comprador.max' => 'El Correo Electrónico debe tener máximo 50 carácteres.',	
            'num_tel_comprador.required' => 'El campo Número Telefonico es obligatorio.',
            'num_tel_comprador.numeric' => 'El campo Número Telefonico debe ser un número.',
            'num_tel_comprador.digits_between' => 'El campo Número Telefonico debe contener 7 dígitos.',
        ];
    }	
}
