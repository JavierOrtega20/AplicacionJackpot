<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class ComercioEditRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
     public function rules()
    {

        return [
            'descripcion'      => 'required|max:50',
			'nombre_sucursal'      => 'required|max:50',
			//'calle_av'      => 'required|max:50',
			//'casa_edif_torre'      => 'required|max:50',
			//'urb_sector'      => 'required|max:50',
			'ciudad'      => 'required|max:50',
			'estado'           => 'required', 
			'fk_id_categoria'           => 'required',  
			'fk_id_subcategoria'           => 'required',
			'estatus'           => 'required',
			'estatus_motivo'           => 'required',
            'direccion'      => 'required',
            'telefono1'      => 'required|min:11|max:15',
            'rif'           => 'required|min:2|max:13',
            'email'           => 'required|email|min:10|max:50',
            'razon_social'      => 'required|min:10|max:50',
            'banco'             => 'required',
            //'codigo_afi_come'   => 'required|unique:comercios,codigo_afi_come,'. $this->id,
            'tasa_cobro_comer'   => 'required|min:1|max:5',
            'tasa_cobro_comer_dolar'   => 'sometimes|required|min:1|max:5',
            'tasa_cobro_comer_euro'   => 'sometimes|required|min:1|max:5',
            'num_cta_princ'     =>  'required|min:20|max:20',
            //'num_cta_secu'      =>  'min:20|max:20',
            'letrarif'           => 'required',   
			'tasa_cobro_comer_stripe'  => 'sometimes|required',  
        ];
    }

    public function messages()
    {
     return [
            'descripcion.required' => 'El campo Nombre es obligatorio',
			'nombre_sucursal.required' => 'El campo Sucursal es obligatorio',
            'descripcion.min' => 'El campo Nombre debe tener al menos 3 carácteres',
            'descripcion.max' => 'El campo Nombre debe tener máximo 50 carácteres',
            'direccion.required' => 'El campo Dirección es obligatorio',
            'telefono1.required' => 'El campo Teléfono 1 es obligatorio',
            'telefono1.min' => 'El Teléfono 1 debe tener al menos 11 dígitos',
            'rif.required' => 'Ingrese el número de Rif sin carácteres especiales.',
            'rif.min' => 'El Rif debe contener 12 dígitos',
            'rif.max' => 'El Rif debe contener 12 dígitos',
            'email.required' => 'El campo Correo Electrónico es obligatorio',
            'email.min' => 'El Correo Electrónico debe tener al menos 10 carácteres',
            'email.min' => 'El Correo Electrónico debe tener al máximo 50 carácteres',
            'razon_social.required' => 'El campo Razón Social es obligatorio',
            'razon_social.min' => 'El campo Razón Social debe tener al menos 10 carácteres',
            'razon_social.max' => 'El campo Razón Social debe tener máximo 50 carácteres',
            'banco.required'            => 'Debe seleccionar un banco',
            //'codigo_afi_come.required' =>  'El Campo Código de Afiliación es obligatorio',
			//'codigo_afi_come.unique' => 'Este código de afiliación ya esta en uso.',
            'tasa_cobro_comer.required'  =>  'El campo Tasa de Cobro de Comisión es obligatorio',
            'tasa_cobro_comer.min'  =>  'El campo Tasa de Cobro de Comisión debe tener al menos 1 dígitos',
            'tasa_cobro_comer.max'  =>  'El campo Tasa de Cobro de Comisión debe tener máximo 4 dígitos',

            'tasa_cobro_comer_dolar.required'  =>  'El campo Tasa de Cobro de Comisión es obligatorio',
            'tasa_cobro_comer_dolar.min'  =>  'El campo Tasa de Cobro de Comisión debe tener al menos 1 dígitos',
            'tasa_cobro_comer_dolar.max'  =>  'El campo Tasa de Cobro de Comisión debe tener máximo 4 dígitos',

            'tasa_cobro_comer_euro.required'  =>  'El campo Tasa de Cobro de Comisión es obligatorio',
            'tasa_cobro_comer_euro.min'  =>  'El campo Tasa de Cobro de Comisión debe tener al menos 1 dígitos',
            'tasa_cobro_comer_euro.max'  =>  'El campo Tasa de Cobro de Comisión debe tener máximo 4 dígitos',
            'num_cta_princ.min'     =>  'El campo Número Cuenta Principal es obligatorio',
            'num_cta_princ.min'     =>  'El campo Debe contener 20 dígitos',
            'num_cta_princ.max'     =>  'El campo Debe contener 20 dígitos',
            'num_cta_secu.min'     =>  'El campo Debe contener 20 dígitos',
            'num_cta_secu.max'     =>  'El campo Debe contener 20 dígitos',
            'letrarif.required'  => 'Seleccione tipo de RIF',
			'estado.required'  => 'Seleccione estado',
			'fk_id_categoria.required'  => 'Seleccione categoría',
			'fk_id_subcategoria.required'  => 'Seleccione sub-categoría',			
			'estatus.required'  => 'Seleccione estatus del comercio',	
			'estatus_motivo.required'  => 'Seleccione motivo del estatus del comercio',	
			'tasa_cobro_comer_stripe.required'  => 'Ingrese la comisión de Stripe'
        ];
    }
}

