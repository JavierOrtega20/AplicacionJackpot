<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class comercios_subcategoria extends Model
{
    protected $table = 'comercios_subcategoria';

    protected $fillable = [
        'id', 'Nombre', 'fk_id_categoria'
    ];
}
