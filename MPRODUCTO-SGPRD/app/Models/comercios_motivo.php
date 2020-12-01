<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class comercios_motivo extends Model
{
    protected $table = 'comercios_motivo';

    protected $fillable = [
        'id', 'nombre'
    ];
}
