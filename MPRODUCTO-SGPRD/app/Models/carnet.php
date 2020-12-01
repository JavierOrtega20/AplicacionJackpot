<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class carnet extends Model
{
    protected $table = 'carnet';

    protected $fillable = [
        'id', 'carnet','carnet_real', 'limite', 'fk_id_miembro', 'fk_id_banco', 'fk_monedas', 'carnet_real', 'disponible', 'transar','cod_emisor','cod_cliente_emisor','tipo_producto','nombre'
    ];

    public function users()
    {
      return $this->belongsToMany(Users::class);
    }

    public function bancos()
    {
      return $this->belongsToMany(bancos::class);
    }
    public function moneda()
    {
      return $this->belongsTo('App\Moneda', 'fk_monedas');
    }

}
