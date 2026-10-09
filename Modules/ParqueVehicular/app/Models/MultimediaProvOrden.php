<?php

namespace Modules\ParqueVehicular\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ParqueVehicular\Database\Factories\MultimediaProvOrdenFactory;

class MultimediaProvOrden extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'folio',
        'ruta',
        'nombre',
        'pv_orden_proveedor_id',
        'descripcion',
        'media_type',
        'categoria',
        'activo'
    ];

    protected $table = 'pv_multimedia_orden';

    protected static function newFactory(): MultimediaProvOrdenFactory
    {
        //return MultimediaProvOrdenFactory::new();
    }
}
