<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UsersEditRequest extends FormRequest
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
            //'nacionalidad' => 'required',
            'dni' => 'required|numeric|digits_between:3,10|unique:users,dni,'. $this->id,
            'first_name' =>'required|min:3|max:40',
            'last_name' => 'required|min:3|max:40',
            'email' => 'required|email|min:10|max:50|unique:users,email,'. $this->id,
			'fk_id_empresas' => 'sometimes|required',
            'num_tel' => 'required|numeric|digits_between:7,7|unique:users,num_tel,'.$this->id.',id,cod_tel'.$validar_codigo,
			'cod_tel' => 'required|unique:users,cod_tel,'.$this->id.',id,cod_tel'.$validar_numero,
            'password' => 'max:15',
            //'password' => 'required',
            'confirm-password' => 'same:password',
            'roles' => 'required',
            'limite' =>'sometimes|required',
            'carnet' => 'sometimes|required|min:16',
            'comercio' => 'required_if:perfil,3',
            'carnets[limite][]' => 'sometimes|required',
            'carnets[fk_monedas][]' => 'sometimes|required',
            'carnets[carnet][]' => 'sometimes|required|min:16',
            'carnets[carnet_real][]' => 'sometimes|required|min:16',
            //'banco' => 'required_if:perfil,5',

            'carnets[emisor][]' => 'sometimes|required',
            'carnets[codClienteEmisor][]' => 'sometimes|required',
        ];


    }

    public function messages()
    {
     return [
            'cod_tel.required' => 'El campo código es obligatorio.',
            'nacionalidad.required' => 'El campo Nacionalidad es obligatorio.',

            'carnets[fk_monedas][].required' => 'El campo Moneda es obligatorio.',
            'carnets[limite][].required' => 'El campo Límite es obligatorio.',

            'carnets[carnet][].required' => 'El campo Tarjeta Virtual es obligatorio.',
            'carnets[carnet][].unique' => 'La tarjeta Virtual que ha ingresado ya está en uso.',
            'carnets[carnet][].min' => 'La Tarjeta Virtual debe contener 16 digitos.',

            'carnets[carnet_real][].required' => 'El campo Tarjeta Real es obligatorio.',
            'carnets[carnet_real][].unique' => 'El Tarjeta Real que ha ingresado ya está en uso.',
            'carnets[carnet_real][].min' => 'El Tarjeta Real debe contener 16 digitos.',

            'carnets[emisor][].required' => 'El campo Emisor es obligatorio.',
            'carnets[codClienteEmisor][].required' => 'El campo Emisor es obligatorio.',


            'dni.required' => 'El campo Cédula es obligatorio.',
            'dni.min' => 'La Cédula debe tener al menos 3 carácteres.',
            'dni.max' => 'La Cédula debe tener máximo 10 carácteres.',
            'dni.numeric' => 'El campo Cédula debe ser un número.',
            'dni.digits_between' => 'El campo Cédula debe contener entre 3 y 10 dígitos.',
			'num_tel.unique' => 'El número de teléfono ya se encuentra en nuestros registros.',
			'cod_tel.unique' => 'El número de teléfono ya se encuentra en nuestros registros.',
            'first_name.required' => 'El campo Nombre es obligatorio.',
            'first_name.min'=> 'El campo Nombre debe tener al menos 3 carácteres.',
            'first_name.max' => 'El campo Nombre debe tener máximo 50 carácteres.',
            'last_name.required' => 'El campo Apellido es obligatorio.',
            'last_name.min'=> 'El campo Apellido debe tener al menos 3 carácteres.',
            'last_name.max' => 'El campo Apellido debe tener máximo 50 carácteres.',
            'email.required' => 'El campo Correo Electrónico es obligatorio.',
            'email.unique' => 'El Correo Electrónico que ha ingresado ya está en uso.',
            'email.email' => 'El campo Correo Electrónico debe ser una dirección de correo válida.',
            'email.min' => 'El Correo Electrónico debe tener al menos 10 carácteres.',
            'email.max' => 'El Correo Electrónico debe tener máximo 50 carácteres.',
			'fk_id_empresas.required' => 'El campo Empresa es obligatorio.',
            //'birthdate.required' => 'El campo Fecha de Nacimiento es obligatorio.',
            //'birthdate.date' => 'El campo Fecha de Nacimiento no corresponde con una fecha válida.',
            'num_tel.required' => 'El campo Número Telefonico es obligatorio.',
            'num_tel.numeric' => 'El campo Número Telefonico debe ser un número.',
            'num_tel.digits_between' => 'El campo Número Telefonico debe contener 7 dígitos.',
            'password.required_if' => 'El campo Contraseña es obligatorio.',
            'password.same' => 'Los campos Contraseña y Confirmar Contraseña deben coincidir.',
            'confirm-password.same' => 'Los campos Contraseña y Confirmar Contraseña deben coincidir.',
            'confirm-password.required' => 'El campos Confirmar Contraseña es obligatorio.',
            'password.min'=> 'El campo Contraseña debe tener al menos 6 carácteres',
            'password.max' => 'El campo Contraseña debe tener máximo 15 carácteres',
            'roles.required' => 'El campo Perfil es obligatorio.',
            'comercio.required_if' => 'El campo Comercio es obligatorio.',
            'limite.required_if' => 'El campo Limite es obligatorio.',
            'carnet.digits_between' => 'El carnet debe contener 16 digitos.',
            'carnet.required_if' => 'El campo Tarjeta Virtual es obligatorio.',
            'banco.required_if' => 'El campo Banco es obligatorio.',
        ];
    }
}
