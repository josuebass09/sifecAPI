<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $cod_barrio
 * @property string $barrio
 * @property string $cod_dis
 * @property string $distrito
 * @property string $cod_can
 * @property string $canton
 * @property int $cod_pro
 * @property string $provincia
 * @property int $cod_pais
 * @property string $pais
 * @property EMISORE[] $eMISORESs
 */
class Ubicacion extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'UBICACIONES';

    /**
     * Indicates if the IDs are auto-incrementing.
     * 
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['id','cod_barrio', 'barrio', 'cod_dis', 'distrito', 'cod_can', 'canton', 'cod_pro', 'provincia', 'cod_pais', 'pais'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function emisores()
    {
        return $this->belongsToMany('App\Emisor', 'id_ubicacion');
    }
}
