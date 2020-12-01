<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class comercios extends Model
{
    use SoftDeletes;
    protected $table = 'comercios';

    protected $fillable = [
        'id', 'descripcion', 'direccion','es_sucursal','nombre_sucursal', 'telefono1', 'telefono2', 'rif', 'email', 'razon_social', 'codigo_afi_come','propina_act', 'estatus', 'estatus_motivo', 'calle_av', 'casa_edif_torre', 'local_oficina', 'urb_sector', 'ciudad', 'estado', 'fk_id_subcategoria', 'fk_id_categoria','posee_sucursales','codigo_afi_real', 'aceptacion_contrato', 'estado_afiliacion_comercio'
    ];

    public function miem_come()
    {
      return $this->hasmany(miem_come::class);
    }

    public function banc_comer()
    {
      return $this->hasmany(banc_comer::class);
    }

    // public function trans_head()
    // {
    //   return $this->hasmany(trans_head::class);
    // }
}
