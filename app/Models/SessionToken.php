<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionToken extends Model
{
    protected $table = 'session_tokens';
    protected $primaryKey = 'idsession';
    public $timestamps = false;

    protected $fillable = [
        'idusuario', 'nombre', 'email', 'token'
    ];
}
