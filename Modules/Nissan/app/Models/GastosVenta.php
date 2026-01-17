<?php

namespace Modules\Nissan\Models;

use App\Traits\AuditorAutos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\GastosVentaFactory;

class GastosVenta extends Model
{
    use HasFactory;
    use AuditorAutos;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
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
        'id_datos_venta',
        'comision_apv_pesos',
        'comision_bdc_pesos',
    ];

    protected $table = 'com_gastos_venta';

    protected $connection = 'autos';


    // Relación con datos de venta
    public function datosVenta()
    {
        return $this->belongsTo(DatosVenta::class, 'id_datos_venta');
    }



    protected static function newFactory(): GastosVentaFactory
    {
        //return GastosVentaFactory::new();
    }
}
