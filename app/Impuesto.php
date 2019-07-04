<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $descripcion
 * @property DETALLECOMPROBANTE[] $dETALLECOMPROBANTESs
 */
class Impuesto extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'IMPUESTOS';

    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     * 
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['descripcion'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function detallecomprobante()
    {
        return $this->belongsToMany('App\DetalleComprobante', 'cod_impuesto');
    }
}
