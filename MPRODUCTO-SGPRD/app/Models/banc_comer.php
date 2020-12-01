<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class banc_comer extends Model
{
    protected $table = 'banc_comer';

    protected $fillable = [
        'id',
        'fk_id_banco',
        'fk_id_comer',
        'tasa_cobro_banco',
        'tasa_cobro_comer',
        'num_cta_princ',
        'num_cta_secu',
        'num_cta_princ_dolar',
        'num_cta_secu_dolar',
        'num_cta_princ_euro',
        'num_cta_secu_euro',
        'tasa_cobro_comer_dolar',
        'tasa_cobro_comer_euro',
		'tasa_cobro_comer_stripe',
		'status_stripe'
    ];

    public function comercios()
    {
      return $this->belongsToMany(comercios::class);
    }

    public function bancos()
    {
      return $this->belongsToMany(bancos::class);
    }
    public function monedas()
    {
      return $this->hasmany(\App\Moneda::class);
    }
}
