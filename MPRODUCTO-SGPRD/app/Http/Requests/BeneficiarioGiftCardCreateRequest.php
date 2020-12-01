<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BeneficiarioGiftCardCreateRequest extends FormRequest
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
       $validar_codigo_receptor=$this->cod_tel_receptor != "" ? ",". $this->cod_tel_receptor:",0";
	   $validar_numero_receptor=$this->num_tel_receptor != "" ? ",". $this->num_tel_receptor:",0";
	   
        return [
            'nacionalidad_receptor' => 'sometimes|required',
            'cedula_receptor' => 'sometimes|required|numeric|digits_between:3,10|unique:users,dni',
            'first_name_receptor' =>'sometimes|required|min:3|max:50',
            'last_name_receptor' => 'sometimes|required|min:3|max:50',
            'email_receptor' => 'sometimes|required|email|min:10|max:50|unique:users,email',
            'cod_tel_receptor' => 'sometimes|required|unique:users,cod_tel,NULL,cod_tel,num_tel'.$validar_numero_receptor,
            'num_tel_receptor' => 'sometimes|required|numeric|digits_between:7,7|unique:users,num_tel,NULL,num_tel,cod_tel'.$validar_codigo_receptor,			
        ];
    }
	
    public function messages()
    {
     return [
            'nacionalidad_receptor.required' => 'El Campo Tipo de documento es obligatorio.',
            'cod_tel_receptor.required' => 'El campo código es obligatorio.',
            'cedula_receptor.required' => 'El campo Cédula es obligatorio.',
            'cedula_receptor.unique' => 'La Cédula ya se encuentra en nuestros registros.',
			'num_tel_receptor.unique' => 'El número de teléfono ya se encuentra en nuestros registros.',
			'cod_tel_receptor.unique' => 'El número de teléfono ya se encuentra en nuestros registros.',
            'cedula_receptor.numeric' => 'El campo Cédula debe ser un número.',
            'cedula_receptor.digits_between' => 'El campo Cédula debe contener entre 3 y 10 dígitos.',
            'first_name_receptor.required' => 'El campo Nombre es obligatorio.',
            'first_name_receptor.min'=> 'El campo Nombre debe tener al menos 3 carácteres.',
            'first_name_receptor.max' => 'El campo Nombre debe tener máximo 50 carácteres.',
            'last_name_receptor.required' => 'El campo Apellido es obligatorio.',
            'last_name_receptor.min'=> 'El campo Apellido debe tener al menos 3 carácteres.',
            'last_name_receptor.max' => 'El campo Apellido debe tener máximo 50 carácteres.',
            'email_receptor.required' => 'El campo Correo Electrónico es obligatorio.',
            'email_receptor.unique' => 'El Correo Electrónico que ha ingresado ya está en uso.',
            'email_receptor.email' => 'El campo Correo Electrónico debe ser una dirección de correo válida.',
            'email_receptor.min' => 'El Correo Electrónico debe tener al menos 10 carácteres.',
            'email_receptor.max' => 'El Correo Electrónico debe tener máximo 50 carácteres.',	
            'num_tel_receptor.required' => 'El campo Número Telefonico es obligatorio.',
            'num_tel_receptor.numeric' => 'El campo Número Telefonico debe ser un número.',
            'num_tel_receptor.digits_between' => 'El campo Número Telefonico debe contener 7 dígitos.',            
        ];
    }	
}
