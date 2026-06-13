<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\VehiculosTokaFactory;

class VehiculosToka extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'com_id_datos_vehiculos', 
        'com_id_tarjetas_toka', 
        'fecha_inicio', 
        'fecha_fin',
        'activo'
    ];

    protected $table = 'com_vehiculos_toka';

    protected static function newFactory(): VehiculosTokaFactory
    {
        //return VehiculosTokaFactory::new();
    }

    public function vehiculo(){
        return $this->belongsTo(DatosVehiculo::class, 'com_id_datos_vehiculos', 'id'); 
    }

    public function tarjetaToka(){
        return $this->belongsTo(TarjetasToka::class, 'com_id_tarjetas_toka', 'id'); 
    }
}
