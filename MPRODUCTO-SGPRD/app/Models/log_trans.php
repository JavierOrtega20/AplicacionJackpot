<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class log_trans extends Model
{
	
    protected $table = 'log_trans';

    protected $fillable = [
    	'id','user_id','trans_id','username','accion','ip','visual'
    ];

}
