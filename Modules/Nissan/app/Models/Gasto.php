<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\GastoFactory;

class Gasto extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // 'id',
        'folio_factura',
        'otros',
        'gasolina',
        'previa',
        'descuentos',
        'traslados',
        'descuento_impulso',
        'total_subsidios',
        'descuento_gastos',
        'cortesia',
        'accesorios',
        'placas',
    ];
     /**
     * Nombre de la tabla
     */
    protected $table = 'com_gastos';
    /**
     * Conexion que se utilizara
     */
    protected $connection = 'autos';

    protected static function newFactory(): GastoFactory
    {
        //return GastoFactory::new();
    }
}
