<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\ObservacionVehiculoFactory;

class ObservacionVehiculo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'observaciones',
        'datos_vehiculo_id'
    ];

    protected $table = 'pv_observaciones_vehiculos';

    public function vehiculo(){
        return $this->belongsTo(DatosVehiculo::class, 'datos_vehiculo_id', 'id');
    }

    protected static function newFactory(): ObservacionVehiculoFactory
    {
        //return ObservacionVehiculoFactory::new();
    }
}
