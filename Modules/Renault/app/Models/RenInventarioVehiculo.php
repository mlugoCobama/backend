<?php

namespace Modules\Renault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RenInventarioVehiculo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'antena',
        'espejo',
        'tapones',
        'rines',
        'tapon_gasolina',
        'radio',
        'encendedor',
        'tapetes',
        'llanta_refaccion',
        'herramientas',
        'reflejantes',
        'extinguidor',
        'cables_corriente',
        'gato',
        'objetos_valor',
        'otros',
        'vestiduras',
        'cristales',
        'ren_entrada_vehiculo_id'
    ];
     /**
     * Nombre de la tabla
     */
    protected $table = 'ren_inventario_vehiculo';
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
     * Un inventario es de una entrada de vehiculo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(RenEntradaVehiculo::class);
    }
}

