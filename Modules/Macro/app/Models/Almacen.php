<?php

namespace Modules\Macro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Models\MovimientosAlmacen;
use Modules\Compras\Transformers\MovimientoAlmacenResource;
use Modules\Macro\Database\Factories\AlmacenFactory;

class Almacen extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fecha_entrada',
        'cant_recibida',
        'existencia',
        'observaciones',
        'com_detalle_solicitud_id'        
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'mcr_almacen';

    protected static function newFactory(): AlmacenFactory
    {
        //return AlmacenFactory::new();
    }
}
