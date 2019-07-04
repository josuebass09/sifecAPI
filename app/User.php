<?php

namespace App;

use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'users';
    protected $fillable = ['email','password','name','id_role'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    /*public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }*/

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany('App\Rol', 'id_role');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function emisor()
    {
        return $this->belongsToMany('App\Emisor', 'id_usuario');
    }

    public function scopeVerificarExistencia($query,$correo,$contrasena)
    {
        return (string)$query->where('email','=',$correo)->where('contrasena','=',$contrasena)
            ->exists();
    }


}
