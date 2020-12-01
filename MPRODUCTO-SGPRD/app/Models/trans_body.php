<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class trans_body extends Model
{
    protected $table = 'trans_body';

    protected $fillable = [
        'id', 'linea', 'fk_id_head', 'fk_dni_miembro', 'fk_id_banco', 'fk_id_comer', 'precio_uni'
    ];

    // public function trans_head()
    // {
    //   return $this->belongsTo(trans_head::class);
    // }
}
