<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PagadorGiftCardExisteRequest extends FormRequest
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
            'carnet' => 'sometimes|required',		
        ];
    }
	
    public function messages()
    {
     return [
            'carnet.required' => 'El Método de pago es obligatorio.',           
        ];
    }	
}
