<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable;
    use EntrustUserTrait { restore as private restoreA; }
    use SoftDeletes { restore as private restoreB; }

    public function restore()
    {
        $this->restoreA();
        $this->restoreB();
    }

/*
    protected $guard = 'user';
    protected $table = 'users';
    protected $primarikey = 'id';
*/
    protected $fillable = [
        'dni', 'nacionalidad', 'first_name', 'last_name', 'image', 'email', 'password', 'kind', 'birthdate', 'cod_tel', 'num_tel','rif','deleted_at','setup', 'fk_id_empresas'
    ];
    protected $date = ['deleted_at'];



    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

/*
    public function miem_ban()
    {
      return $this->hasmany(miem_ban::class);
    }

    public function trans_head()
    {
      return $this->hasmany(trans_head::class);
    }

    public function miem_come()
    {
      return $this->hasmany(miem_come::class);
    }
*/
    public function carnets()
    {
      return $this->hasmany(carnet::class, 'fk_id_miembro');
    }
    public function monedas()
    {
      return $this->hasmany(\App\Moneda::class, 'user_id');
    }
}
