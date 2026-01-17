<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\TipoVentaFactory;

class TipoVenta extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'porcentaje',
    ];

    protected $table = 'com_tipos_venta';

    protected $connection = 'autos';


    // Relación: un tipo de venta puede tener muchas ventas
    public function datosVentas()
    {
        return $this->hasMany(DatosVenta::class, 'tipo_venta_id');
    }

    protected static function newFactory(): TipoVentaFactory
    {
        //return TipoVentaFactory::new();
    }
}
