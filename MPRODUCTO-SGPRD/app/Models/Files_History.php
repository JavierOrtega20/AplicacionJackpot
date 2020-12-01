<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Files_History extends Model
{
    protected $table = 'files_history';

    protected $fillable = [
        'id', 'Filename', 'ProcessType','email','user_id','ip'
    ];


}