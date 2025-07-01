<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\DatosVehiculoFactory;

class DatosVehiculo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'marca', 
        'submarca',
        'modelo',
        'no_serie',
        'placas',
        'id_sucursal',
    ];
     /**
     * Nombre de la tabla
     */
    protected $table = 'com_datos_vehiculos';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Un tanque pertenece a un vehículo.
     */
    public function datos_tanque(){
        return $this->hasOne(DatosTanque::class,'com_datos_vehiculo_id' ,'id' );
    }

    protected static function newFactory(): DatosVehiculoFactory
    {
        //return DatosVehiculoFactory::new();
    }
}
