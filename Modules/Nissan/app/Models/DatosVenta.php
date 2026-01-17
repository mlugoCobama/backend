<?php

namespace Modules\Nissan\Models;

use App\Traits\AuditorAutos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\DatosVentaFactory;

class DatosVenta extends Model
{
    use HasFactory;
    use AuditorAutos;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fecha_as_salida',
        'no_factura',
        'razon_social',
        'descripcion', 
        'no_inventario',
        'id_vendedor',
        'serie',
        'total_venta',
        'costos',
        'bonificaciones',
        'utilidad_inicial',
        'tipo_venta_id',
        'tipo_venta',
        'clave_producto',
        'modelo_producto',
        'anio_vehiculo',
        'estatus',
        'entregado',
        'bdc',
        'agencia',
        'razon_social',
        'fecha_factura',
        'validado',
        'pagado',
        'observacion'
    ];

    protected $table = 'com_datos_venta';

    protected $connection = 'autos';


    // Relación con vendedor
    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'id_vendedor', 'id');
    }

    // Relación con tipo de venta
    public function tipoVenta()
    {
        return $this->belongsTo(TipoVenta::class, 'tipo_venta_id', 'id');
    }

    // Relación con gastos de venta
    public function gatosVenta()
    {
        return $this->hasOne(GastosVenta::class, 'id_datos_venta', 'id');
    }

    public function scopeEntregados($query){
        return $query->where('entrgado', '1');
    }

    public function scopeNoEntregados($query){
        return $query->where('entrgado', '1');
    }



    protected static function newFactory(): DatosVentaFactory
    {
        //return DatosVentaFactory::new();
    }
}
