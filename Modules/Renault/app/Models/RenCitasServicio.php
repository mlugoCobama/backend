<?php

namespace Modules\Renault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Renault\Database\Factories\RenCitasServicioFactory;

class RenCitasServicio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'folio',
        'empleado_id',
        'fecha',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'rfc',
        'telefono',
        'domicilio',
        'email',
        'vin',
        'modelo',
        'placas',
        'color',
        'tipo',
        'anio',
        'kilometraje',
        'observaciones',
        'tipo_cita',
        'estatus',
    ];

   /**
     * Nombre de la tabla
     */
    protected $table = 'ren_citas_servicio';
    /**
     * Conexion que se utilizara
     */
    protected $connection = 'autos';
}
