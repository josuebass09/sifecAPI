<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $id_comprobante
 * @property string $unidad_medida
 * @property string $tp_articulo
 * @property string $cod_impuesto
 * @property string $tp_documento_exo
 * @property int $numero_linea
 * @property float $cantidad
 * @property string $unidad_medida_comercial
 * @property string $detalle
 * @property float $precio_unitario
 * @property float $monto_total
 * @property float $monto_descuento
 * @property string $naturaleza_descuento
 * @property float $subtotal
 * @property float $monto_impuesto
 * @property float $tarifa
 * @property string $numero_doc_exo
 * @property string $institucion_exo
 * @property string $fecha_emision_exo
 * @property float $monto_impuesto_exo
 * @property int $porcentaje_compra_exo
 * @property float $monto_total_linea
 * @property COMPROBANTE $cOMPROBANTE
 * @property IMPUESTO $iMPUESTO
 * @property TPARTICULO $tPARTICULO
 * @property TPDOCUMENTO $tPDOCUMENTO
 * @property UNIDADESMEDIDA $uNIDADESMEDIDA
 */
class DetalleComprobante extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'DETALLE_COMPROBANTES';
    protected $primaryKey= 'id';
    public $timestamps = false;
    /**
     * @var array
     */
    protected $fillable = ['id_comprobante','tp_articulo', 'cod_impuesto', 'tp_documento_exo', 'numero_linea', 'cantidad', 'unidad_medida_comercial', 'detalle', 'precio_unitario', 'monto_total', 'monto_descuento', 'naturaleza_descuento', 'subtotal','monto_total_linea'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function comprobante()
    {
        return $this->hasOne('App\Comprobante', 'id_comprobante');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    /*public function impuesto()
    {
        return $this->belongsToMany('App\Impuesto', 'cod_impuesto');
    }*/

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tparticulo()
    {
        return $this->hasOne('App\TPArticulo', 'tp_articulo');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tpdocumento()
    {
        return $this->hasOne('App\TPDocumento', 'tp_documento_exo');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function unidadmedida()
    {
        return $this->hasOne('App\UnidadMedida', 'unidad_medida');
    }
}
