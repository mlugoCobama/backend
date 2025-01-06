<?php

namespace Modules\Renault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RenEntradaVehiculo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'folio',
        'fecha',
        'num_entrada',
        'ren_citas_servicio_id'
    ];
     /**
     * Nombre de la tabla
     */
    protected $table = 'ren_entrada_vehiculo';
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
     * Una entrada de vehiculo tiene un inventario
     */
    public function Inventario(): HasOne {
        return $this->hasOne(RenInventarioVehiculo::class);
    }
    /**
     * Una entrada de vehiculo tiene varios testigos fotograficos
     */
    public function TestigosFotograficos(): HasMany {
        return $this->hasMany(RenTestigosFotograficos::class);
    }

}
