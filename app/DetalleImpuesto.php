<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetalleImpuesto extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'DETALLE_IMPUESTOS';
    protected $primaryKey= 'id';
    public $timestamps = false;
    /**
     * @var array
     */
    protected $fillable = ['id_comprobante','id_linea','id_impuesto','tarifa','monto','tp_documento_exo','numero_doc_exo','institucion_exo','fecha_emision_exo','monto_impuesto_exo','porcentaje_compra_exo','codigo_tarifa'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function impuesto()
    {
        return $this->belongsToMany('App\Impuesto', 'id_impuesto');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function comprobante()
    {
        return $this->hasOne('App\Comprobante', 'id_comprobante');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function detalleComprobante()
    {
        return $this->hasOne('App\DetalleComprobante', 'id_linea');
    }
}
