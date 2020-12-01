<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class canal extends Model
{
    protected $table = 'canal';

    protected $fillable = [
        'id', 'Nombre'
    ];
}
