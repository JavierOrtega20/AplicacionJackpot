<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class canal_comer extends Model
{
    protected $table = 'canal_comer';

    protected $fillable = [
        'id', 'fk_id_comer', 'num_terminales','canal_virtual','canal_fisico','fk_id_canal'
    ];
}
