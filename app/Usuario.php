<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * @property string $id
 * @property int $role_id
 * @property string $descripcion
 * @property string $contrasena
 * @property string $fecha_creacion
 * @property string $email
 * @property ROLE $rOLE
 * @property EMISORE[] $eMISORESs
 */
class Usuario extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'USUARIOS';

    /**
     * @var array
     */
    protected $fillable = ['descripcion', 'contrasena', 'fecha_creacion', 'email'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany('App\Rol', 'role_id');
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
