<?php

namespace Modules\Macro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Macro\Database\Factories\SeguroVehiculoFactory;

class SeguroVehiculo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
     /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'aseguradora',
        'inciso_vehiculo',
        'cobertura',
        'inicio_vigencia',
        'fin_vigencia',
        'flotilla',
        'inciso_foltilla',
        'fecha_renovacion',
        'id_com_datos_vehiculo',
        'activo',
        
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'mcr_seguro_vehiculos';

    protected static function newFactory(): SeguroVehiculoFactory
    {
        //return SeguroVehiculoFactory::new();
    }
}
