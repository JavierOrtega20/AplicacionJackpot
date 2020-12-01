<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class miem_ban extends Model
{
    protected $table = 'miem_ban';

    protected $fillable = [
        'id', 'fk_dni_miembro', 'fk_id_banco', 'fecha_afili', 'credito_apro', 'fk_id_limite', 'credito_disp','tasa_cobro_prop','monto_prop','nro_cta_propina','tasa_cobro_cliente'
    ];
   
    public function users()
    {
      return $this->belongsToMany(Users::class);
    }

    public function bancos()
    {
      return $this->belongsToMany(bancos::class);
    }

     public function limite_cre()
    {
      return $this->hasOne(limite_cre::class);
    }
}
