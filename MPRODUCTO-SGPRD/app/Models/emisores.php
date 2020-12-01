<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class emisores extends Model
{
    protected $table = 'emisores';
      protected $primaryKey = 'id';
        protected $fillable = ['id', 'cod_emisor', 'nombre', 'created_at', 'updated_at', 'producto', 'fk_monedas', 'tipo', 'categoria', 'descripcion', 'lema', 'bin', 'rif', 'paga_comision', 'monto_fijo', 'tasa_comision', 'monto_minimo', 'dias_vencimiento','requiere_pin','pin'];
}
