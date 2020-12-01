<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class miem_come extends Model
{
    protected $table = 'miem_come';

    protected $fillable = [
        'id', 'fk_id_miembro', 'fk_id_comercio'
    ];

    public function users()
    {
      return $this->belongsToMany(Users::class);
    }

    public function comercios()
    {
      return $this->belongsToMany(comercios::class);
    }
}
