<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class log extends Model
{
	
    protected $table = 'log';

    protected $fillable = [
    	'id','user_id','accion','ip'
    ];

}
