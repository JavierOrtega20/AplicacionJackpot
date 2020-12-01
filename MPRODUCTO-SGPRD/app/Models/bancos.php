<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class bancos extends Model
{
    protected $table = 'bancos';

    protected $fillable = [
        'id', 'descripcion', 'telefono1', 'telefono2', 'rif', 'contacto', 'codigo_afi_banc'
    ];

    public function carnet()
    {
      return $this->hasmany(carnet::class);
    }

    public function banc_comer()
    {
      return $this->hasmany(banc_comer::class);
    }

    // public function trans_head()
    // {
    //   return $this->hasmany(trans_head::class);
    // }

    public function miem_ban()
    {
      return $this->hasmany(miem_ban::class);
    }

}
