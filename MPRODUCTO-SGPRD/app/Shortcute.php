<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shortcute extends Model
{
    protected $table = 'shortcute';
    protected $primaryKey = 'short_id';

    protected $fillable = [
        'short_id', 'short_hash', 'short_link'
    ];
}
