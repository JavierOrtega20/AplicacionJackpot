<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ledge extends Model
{
  
    //
     protected $table = 'ledger';
    
     protected $fillable = [
		'id','fk_id_trans_head','fk_dni_miembros','monto','propina','disp_pre','disp_post', 'carnet_id',
    ];

    public function trans_head()
    {
      return $this->belongsToMany(Ledges::class);
    }

    public function users()
    {
      return $this->belongsToMany(Users::class);
    }

    public function carnet()
    {
      return $this->belongsTo(carnet::class);
    }
    
}
