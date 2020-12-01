<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class terminal extends Model
{
    protected $table = 'terminal';

    protected $fillable = [
        'id', 'status', 'codigo_terminal_comercio','serial','fk_id_comer_canal'
    ];
}
