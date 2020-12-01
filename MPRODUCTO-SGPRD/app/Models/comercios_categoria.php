<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class comercios_categoria extends Model
{
    protected $table = 'comercios_categoria';

    protected $fillable = [
        'id', 'Nombre'
    ];
}
