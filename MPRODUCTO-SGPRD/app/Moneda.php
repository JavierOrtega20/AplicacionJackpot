<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Moneda extends Model
{
    protected $table = 'monedas';
      protected $primaryKey = 'mon_id';
        protected $fillable = ['mon_nombre','mon_simbolo', 'user_id', 'mon_status'];
}
