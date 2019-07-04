<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $id_usuario
 * @property string $id_tpidentificacion
 * @property int $id_ubicacion
 * @property string $razon_social
 * @property string $nombre_comercial
 * @property string $usuario_atv_prod
 * @property string $contrasena_atv_prod
 * @property string $certificado_atv_prod
 * @property string $pin_atv_prod
 * @property string $usuario_atv_test
 * @property string $contrasena_atv_test
 * @property string $certificado_atv_test
 * @property string $pin_atv_test
 * @property int $creditos_usados
 * @property int $creditos_disponibles
 * @property string $vencimiento_plan
 * @property string $api_key
 * @property boolean $activo
 * @property string $otras_senas
 * @property int $consecutivoFEprod
 * @property int $consecutivoTEprod
 * @property int $consecutivoNCprod
 * @property int $consecutivoNDprod
 * @property int $consecutivoGAprod
 * @property int $consecutivoFEtest
 * @property int $consecutivoTEtest
 * @property int $consecutivoNCtest
 * @property int $consecutivoNDtest
 * @property int $consecutivoGAtest
 * @property string $host_smtp_nova
 * @property string $usuario_smtp_nova
 * @property string $contrasena_smtp_nova
 * @property string $metodo_smtp_nova
 * @property int $puerto_smtp_nova
 * @property string $host_smtp_secundario
 * @property string $usuario_smtp_secundario
 * @property string $contrasena_smtp_secundario
 * @property string $metodo_smtp_secundario
 * @property int $puerto_smtp_secundario
 * @property TPIDENTIFICACIONE $tPIDENTIFICACIONE
 * @property UBICACIONE $uBICACIONE
 * @property USUARIO $uSUARIO
 * @property COMPROBANTE[] $cOMPROBANTESs
 * @property GASTO[] $gASTOSs
 * @property string telefono
 * @property string fax
 */
class Emisor extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'EMISORES';
    protected $primaryKey= 'id';
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = ['id_usuario','razon_social','id_tpidentificacion', 'nombre_comercial', 'usuario_atv_prod', 'contrasena_atv_prod', 'certificado_atv_prod', 'pin_atv_prod', 'usuario_atv_test', 'contrasena_atv_test', 'certificado_atv_test', 'pin_atv_test', 'creditos_usados', 'creditos_disponibles', 'vencimiento_plan', 'api_key', 'activo', 'otras_senas', 'consecutivoFEprod', 'consecutivoTEprod', 'consecutivoNCprod', 'consecutivoNDprod', 'consecutivoGAprod', 'consecutivoFEtest', 'consecutivoTEtest', 'consecutivoNCtest', 'consecutivoNDtest', 'consecutivoGAtest', 'host_smtp_nova', 'usuario_smtp_nova', 'contrasena_smtp_nova', 'metodo_smtp_nova', 'puerto_smtp_nova', 'host_smtp_secundario', 'usuario_smtp_secundario', 'contrasena_smtp_secundario', 'metodo_smtp_secundario', 'puerto_smtp_secundario','correo','logo','SMTP_OP','PDF','consecutivoFECtest','consecutivoFEEtest','consecutivoFECprod','consecutivoFEEprod','telefono','fax'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tpidentificacion()
    {
        return $this->hasOne('App\TPIdentificacion', 'id_tpidentificacion');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ubicacion()
    {
        return $this->hasOne('App\Ubicacion', 'id_ubicacion');
    }



    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function usuario()
    {
        return $this->hasOne('App\Usuario', 'id_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comprobante()
    {
        return $this->hasMany('App\Comprobante', 'id_emisor');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function gasto()
    {
        return $this->hasMany('App\Gasto', 'id_receptor');
    }

}
