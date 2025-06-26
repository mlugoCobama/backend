<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\PorcentajeFactory;

class Porcentaje extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // 'id',
        'tipo_venta',
        'porcentaje_apv',
        'porcentaje_bdc',
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'com_porcentajes';
    /**
     * Conexion que se utilizara
     */
    protected $connection = 'autos';
    
    protected static function newFactory(): PorcentajeFactory
    {
        //return PorcentajeFactory::new();
    }
}
