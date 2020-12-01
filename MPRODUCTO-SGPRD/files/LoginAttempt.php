<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $fillable = [
        'id', 'ip', 'user_id', 'succesfull'
    ];

}
