<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class trans_head extends Model
{
    protected $table = 'trans_head';

    protected $fillable = [
        'id','referencia','fk_dni_miembros', 'fk_id_banco', 'fk_id_comer', 'monto','comision','neto','monto_propina','porc_propina', 'cancela_a', 'token', 'reverso', 'status','procesado','ip', 'token_status', 'toke_time', 'fk_monedas', 'carnet_id','origen','TerminalId','otp_bco','otp_bco_time','reverse_bco_ref','reverse_bco_time','ref_bco','trans_bco_time','rompe_liquidacion'
    ];

    public function trans_body()
    {
      return $this->hasmany(trans_body::class);
    }

    public function bancos()
    {
      return $this->belongsToMany(bancos::class);
    }

    public function comercios()
    {
      return $this->belongsToMany(comercios::class);
    }

    public function carnets()
    {
      return $this->belongsTo(carnet::class);
    }

    public function users()
    {
      return $this->belongsToMany(Users::class);
    }

    public function monedas()
    {
      return $this->hasmany(\App\Moneda::class);
    }

    public function canal()
    {
      return $this->belongsToMany(canal::class);
    }

    public function canal_comer()
    {
      return $this->belongsToMany(canal_comer::class);
    }

    public function scopeFecha($query, $fechaTrans){
     //dd('scope' . $sede);

     if (trim($fechaTrans) != '') {
      
        // dd("scope: ". $fechaTrans);
       //$fecha = strtoupper($fecha);
           $query->where('trans_head.created_at', $fechaTrans);

     }

   }
}
