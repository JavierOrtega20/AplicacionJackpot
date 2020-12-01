<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GiftEditRequest extends FormRequest
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
            'nombre'      => 'required|max:200',
			'descripcion'      => 'required|max:200',
			'monto_minimo'      => 'required|max:6',
			'dias_vencimiento'      => 'required|max:3',
			'p_comision'      => 'required|max:6',
			'm_comision_fijo'      => 'required|max:6',
            'IMGgOtros'           => 'mimes:jpeg,jpg,png|max:100',
			'IMGg25'           => 'mimes:jpeg,jpg,png|max:100',
			'IMGg50'           => 'mimes:jpeg,jpg,png|max:100',
			'IMGg100'           => 'mimes:jpeg,jpg,png|max:100',
			'IMGg200'           => 'mimes:jpeg,jpg,png|max:100',
			'pin'      			=> 'sometimes|required|max:6|min:6',			
        ];
    }

    public function messages()
    {
     return [
            'comercio_emisor.required' => 'Seleccione Comercio Emisor',
			'nombre.required' => 'El campo Nombre es obligatorio',
            'nombre.max' => 'El campo Nombre debe tener maximo 50 digitos',
			'descripcion.required' => 'El campo Descripcion es obligatorio',
            'descripcion.max' => 'El campo Descripcion debe tener maximo 50 digitos',
			'monto_minimo.required' => 'El campo Monto Mínimo es obligatorio',
            'monto_minimo.max' => 'El campo Monto Mínimo debe tener maximo 5 digitos',				
			'dias_vencimiento.required' => 'El campo Vencimiento en días es obligatorio',
            'dias_vencimiento.max' => 'El campo Días de Vencimiento debe tener maximo 5 digitos',							
			'p_comision.required' => 'El campo Porcentaje Comisión es obligatorio',
            'p_comision.max' => 'El campo Porcentaje Comisión debe tener maximo 5 digitos sin incluir el separador de decimales',
			'm_comision_fijo.required' => 'El campo Comisión Fija es obligatorio',
            'm_comision_fijo.max' => 'El campo Comisión Fija debe tener maximo 5 digitos sin incluir el separador de decimales',
			'IMGgOtros.mimes' => 'La imagen debe ser png o jpeg',
			'IMGgOtros.max' => 'La imagen no debe pesar mas de 100kb',
			'IMGg25.mimes' => 'La imagen debe ser png o jpeg',
			'IMGg25.max' => 'La imagen no debe pesar mas de 100kb',
			'IMGg50.mimes' => 'La imagen debe ser png o jpeg',
			'IMGg50.max' => 'La imagen no debe pesar mas de 100kb',
			'IMGg100.mimes' => 'La imagen debe ser png o jpeg',
			'IMGg100.max' => 'La imagen no debe pesar mas de 100kb',
			'IMGg200.mimes' => 'La imagen debe ser png o jpeg',
			'IMGg200.max' => 'La imagen no debe pesar mas de 100kb',
			'pin.required' => 'Cuando active el PIN de Seguridad debe especificar uno.',			
			'pin.max' => 'El PIN de seguridad debe contener 6 dígitos numéricos',
			'pin.min' => 'El PIN de seguridad debe contener 6 dígitos numéricos',
        ];
    }
}