<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $tp_emisor
 * @property string $tp_receptor
 * @property string $id_receptor
 * @property string $clave_comprobante
 * @property string $clave_gasto
 * @property string $consecutivo_recepcion
 * @property string $identificacion_emisor
 * @property string $fecha_gasto
 * @property int $mensaje
 * @property string $detalle
 * @property float $total_impuestos
 * @property float $total_comprobante
 * @property string $xml_completo
 * @property string $xml_result
 * @property string $estado
 * @property string $respuesta_api
 * @property string $http_hacienda
 * @property string $location
 * @property EMISORE $eMISORE
 * @property TPIDENTIFICACIONE $tPIDENTIFICACIONE

 */
class Gasto extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'GASTOS';
    protected $primaryKey= 'id';
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = ['clave_comprobante', 'clave_gasto', 'consecutivo_recepcion', 'identificacion_emisor', 'fecha_gasto', 'mensaje', 'detalle', 'total_impuestos', 'total_comprobante', 'xml_completo', 'xml_result', 'estado', 'respuesta_api', 'http_hacienda','location'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function receptortp()
    {
        return $this->hasOne('App\Emisor', 'id_receptor');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function emisortp()
    {
        return $this->hasOne('App\TPIdentificacion', 'tp_emisor');
    }



}
