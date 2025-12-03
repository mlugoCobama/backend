<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\AlmacenComprasFactory;

class AlmacenCompras extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fecha_actualizacion',
        'existencia',
        'cantidad',
        'com_detalle_solicitud_id',
        'codigo_producto',
        'id_usuario'
    ];
    protected $table =  'com_almacen';

    protected static function newFactory(): AlmacenComprasFactory
    {
        //return AlmacenComprasFactory::new();
    }
}
