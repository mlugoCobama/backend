<?php

namespace Modules\Renault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Relations\HasOne;


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
        'agencia_id',
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'ren_citas_servicio';
    /**
     * Conexion que se utilizara
     */
    protected $connection = 'autos';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Una proveedor tiene varios documentos
     */
    public function Datos(): HasOne {
        return $this->hasOne(RenEntradaVehiculo::class);
    }

    public function eventosCita(){
        return $this->hasMany(RenEventosCita::class, 'ren_citas_servicio_id', 'id' );
    }
    public function encuesta(){
        return $this->hasOne(RenEncuestaCita::class, 'ren_citas_servicio_id', 'id');
    }
}
