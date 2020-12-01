<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class giftcard_imagenes extends Model
{
    protected $table = 'giftcard_imagenes';
      protected $primaryKey = 'id';
        protected $fillable = ['id', 'fk_giftcard', 'monto', 'nombre_imagen', 'created_at', 'updated_at'];
}
