<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class UsersRequest extends FormRequest
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
            'nacionalidad' => 'required',
            'dni' => 'required|numeric|digits_between:3,10|unique:users,dni',
            'first_name' =>'required|min:3|max:50',
            'last_name' => 'required|min:3|max:50',
            'email' => 'required|email|min:10|max:50|unique:users,email',
            //'birthdate' => 'date',
            'num_tel' => 'required|numeric|digits_between:7,7|unique:users,num_tel,NULL,num_tel,cod_tel'.$validar_codigo,
            'password' => 'sometimes|required|min:6|max:15',
            //'password' => 'sometimes|required|same:confirm-password|min:6|max:15',
            'confirm-password' => 'sometimes|required|same:password|min:6|max:15',
            'roles' => 'sometimes|required',
            'comercio' => 'required_if:perfil,3',
            'limite' => 'sometimes|required',
			'empresa' => 'sometimes|required',
            //'carnet' => 'sometimes|required|min:16|unique:carnet,carnet',
            //'banco' => 'required_if:perfil,5',
            'cod_tel' => 'required|unique:users,cod_tel,NULL,cod_tel,num_tel'.$validar_numero,
            'nacionalidad' => 'required',
            'carnets[fk_monedas][]' => 'sometimes|required',
            //'carnets' => 'sometimes|required|unique:carnet,  carnets',

            "carnets[carnet][]" => "sometimes|required|array|min:16",
            //"carnets[carnet][].*" => "required|string|distinct|min:16",

            'carnets[limite][]' => 'sometimes|required',
            'carnets[carnet_real][]' => 'sometimes|required|min:16',

            'carnets[emisor][]' => 'sometimes|required',
            'carnets[codClienteEmisor][]' => 'sometimes|required', 
            'carnets[tipoProducto][]' => 'sometimes|required',

            
        ];

    }
    public function messages()
    {
     return [
            'nacionalidad.required' => 'El Campo Tipo de documento es obligatorio.',

            'carnets[fk_monedas][].required' => 'El campo Moneda es obligatorio.',
            'carnets[limite][].required' => 'El campo Límite es obligatorio.',

            'carnets[carnet][].required' => 'El campo Tarjeta Virtual es obligatorio.',
            'carnets[carnet][].unique' => 'El Tarjeta Virtual que ha ingresado ya está en uso.',
            'carnets[carnet][].min' => 'El Tarjeta Virtual debe contener 16 digitos.',

            'carnets[carnet_real][].required' => 'El campo Tarjeta Real es obligatorio.',
            'carnets[carnet_real][].unique' => 'El Tarjeta Real que ha ingresado ya está en uso.',
            'carnets[carnet_real][].min' => 'El Tarjeta Real debe contener 16 digitos.',

            'carnets[emisor][].required' => 'El campo Emisor es obligatorio.',
            'carnets[codClienteEmisor][].required' => 'El campo Emisor es obligatorio.',
            'carnets[tipoProducto][].required' => 'El campo Emisor es obligatorio.',


            'cod_tel.required' => 'El campo código es obligatorio.',
            'dni.required' => 'El campo Cédula es obligatorio.',
            'dni.unique' => 'La Cédula ya se encuentra en nuestros registros.',
			'num_tel.unique' => 'El número de teléfono ya se encuentra en nuestros registros.',
			'cod_tel.unique' => 'El número de teléfono ya se encuentra en nuestros registros.',
            'dni.numeric' => 'El campo Cédula debe ser un número.',
            'dni.digits_between' => 'El campo Cédula debe contener entre 3 y 10 dígitos.',
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
			'empresa.required' => 'El campo Empresa es obligatorio.',
            //'birthdate.required' => 'El campo Fecha de Nacimiento es obligatorio.',
            //'birthdate.date' => 'El campo Fecha de Nacimiento no corresponde con una fecha válida.',
            'num_tel.required' => 'El campo Número Telefonico es obligatorio.',
            'num_tel.numeric' => 'El campo Número Telefonico debe ser un número.',
            'num_tel.digits_between' => 'El campo Número Telefonico debe contener 7 dígitos.',
            'password.required' => 'El campo Contraseña es obligatorio.',
            'confirm-password.same' => 'Los campos Contraseña y Confirmar Contraseña deben coincidir.',
            'confirm-password.required' => 'El campos Confirmar Contraseña es obligatorio.',
            'password.min'=> 'El campo Contraseña debe tener al menos 6 carácteres',
            'password.max' => 'El campo Contraseña debe tener máximo 15 carácteres',
            'roles.required' => 'El campo Perfil es obligatorio.',
            'comercio.required_if' => 'El campo Comercio es obligatorio.',
            'limite.required_if' => 'El campo Limite es obligatorio.',
            'carnet.required_if' => 'El campo Tarjeta Virtual es obligatorio.',
            'carnet.unique' => 'El número de Tarjeta Virtual ya está en uso.',
            'banco.required_if' => 'El campo Banco es obligatorio.',
        ];
    }
}
