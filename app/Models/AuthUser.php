<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AuthUser extends Authenticatable
{
    protected $table = 'usuario'; // tu tabla real
    protected $primaryKey = 'idusuario';
    public $timestamps = false;

    protected $fillable = [
        'nombre', 'celular', 'username', 'email', 'password', 'activo', 'idrol'
    ];

    protected $hidden = ['password'];
}
