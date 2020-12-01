<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class comercios_estatus extends Model
{
    protected $table = 'comercios_estatus';

    protected $fillable = [
        'id', 'nombre'
    ];
}
