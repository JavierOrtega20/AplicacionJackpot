<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Automatic_Files extends Model
{
    protected $table = 'automatic_files';

    protected $fillable = [
        'id', 'Filename', 'TotalRows', 'TotalProcessed', 'TotalErrors', 'ProcessType', 'ErrorDetail', 'processed','InProgress','email','user_id','ip'
    ];


}
