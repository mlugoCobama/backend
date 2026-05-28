<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\DatosGpsFactory;

class DatosGps extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'com_datos_vehiculos_id',
        'distancia_metros',
        'distancia_km',
        'veces_detenido',
        'tiempo_detenido_segundos',
        'tiempo_manejando_segundos',
        'fecha'
    ];

    protected $table = 'com_datos_gps';

    /**
     * Un dato de gps pertence a un vehiculo
     */
    public function vehiculo(){
        return $this->belongsTo(DatosVehiculo::class, 'com_datos_vehiculos_id', 'id');
    }

    protected static function newFactory(): DatosGpsFactory
    {
        //return DatosGpsFactory::new();
    }
}
