<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class trans_gift_card extends Model
{
	
    protected $table = 'trans_gift_card';

    protected $fillable = [
    	'id','fk_trans_id','fk_dni_recibe','fk_carnet_id_recibe','monto', 'comision_monto', 'vencimiento', 'pago_comision', 'giftcard_id', 'imagen'
    ];

}
