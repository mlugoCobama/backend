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
        'aseguradora',              //* Nombre aseguradora
        'cobertura',                // Descripcion cobertura contatadas (Ej, Responsabilidad civil, Amplia, Etc.)
        'fecha_renovacion',         // Fecha de emision, contratación de la póliza
        'inicio_vigencia',          // Fecha de inicio de vigencia        
        'fin_vigencia',             // Fecha de termino de vigencia
        'flotilla',                 // Numero de poliza
        'inciso_foltilla',          // Numero (Inciso) que ocupa el vehículo dentro de la póliza
        'id_com_datos_vehiculo',    // Relación con el vehículo
        'activo',                   // Borrado lógico
        'ramo',                     // Categoría que asegura
        'sub_ramo',                 // Tipo de vehículo asegurado
        'tipo_movimiento',          // Flotilla o Vehiculo Individual
        'prima_total',              // Costo total de seguro por vehiculo
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
