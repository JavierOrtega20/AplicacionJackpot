<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class limite_cre extends Model
{
    protected $table = 'limite_cre';

    protected $fillable = [
        'id', 'descripcion'
    ];

    public function miem_ban()
    {
      return $this->belongsTo(miem_ban::class);
    }
}
