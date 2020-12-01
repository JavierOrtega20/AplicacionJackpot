<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class estados extends Model
{
    protected $table = 'estados';

    protected $fillable = [
        'id', 'nombre'
    ];
}
