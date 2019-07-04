<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_emisor
 * @property string $tp_comprobante
 * @property string $tp_receptor
 * @property string $fecha_emision
 * @property string $clave
 * @property string $numeracion
 * @property string $codigo_moneda
 * @property float $tp_cambio
 * @property string $id_receptor
 * @property string $nombre_receptor
 * @property string $json_recibido
 * @property string $xml_firmado
 * @property string $xml_result
 * @property float $subtotal_comprobante
 * @property float $total_impuestos
 * @property float $total_comprobante
 * @property string $http_hacienda
 * @property string $respuesta_api
 * @property EMISORE $eMISORE
 * @property TPCOMPROBANTE $tPCOMPROBANTE
 * @property TPIDENTIFICACIONE $tPIDENTIFICACIONE
 * @property DETALLECOMPROBANTE[] $dETALLECOMPROBANTESs
 * @property string $cc
 */
class Comprobante extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'COMPROBANTES';
    protected $primaryKey= 'id';
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = ['tp_receptor','id_emisor', 'fecha_emision', 'clave', 'numeracion', 'codigo_moneda', 'tp_cambio', 'id_receptor', 'nombre_receptor', 'json_recibido', 'xml_firmado', 'xml_result', 'subtotal_comprobante', 'total_impuestos', 'total_comprobante', 'http_hacienda', 'respuesta_api','tp_comprobante','estado','email_receptor','tp_condicion_venta','tp_medio_pago','tp_accion_referencia','tp_doc_referencia','numero_referencia','fecha_emision_ref','razon_referencia','cc'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function emisor()
    {
        return $this->hasOne('App\Emisor', 'id_emisor');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tpcomprobante()
    {
        return $this->hasOne('App\TPComprobante', 'tp_comprobante');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tpreceptor()
    {
        return $this->hasOne('App\TPIdentificacion', 'tp_receptor');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function detallescomprobante()
    {
        return $this->belongsToMany('App\DetalleComprobante', 'id_comprobante');
    }
}
