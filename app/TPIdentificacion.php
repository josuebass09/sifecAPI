<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $descripcion
 * @property COMPROBANTE[] $cOMPROBANTESs
 * @property EMISORE[] $eMISORESs
 * @property GASTO[] $gASTOSsemi
 * @property GASTO[] $gASTOSsrec
 */
class TPIdentificacion extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'TP_IDENTIFICACIONES';

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function comprobantetpreceptor()
    {
        return $this->belongsTo('App\Comprobante', 'tp_receptor');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function emisortpidentificacion()
    {
        return $this->belongsTo('App\Emisor', 'id_tpidentificacion');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gastotpemisor()
    {
        return $this->belongsTo('App\Gasto', 'tp_emisor');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gastotpreceptor()
    {
        return $this->belongsTo('App\Gasto', 'tp_receptor');
    }
}
