<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\ComTomaUnidadFactory;

class ComTomaUnidad extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'com_vendedores_id',
        'no_inventario',
        'clave_producto',
        'anio_vehiculo',
        'comision_apv_pesos',
        'fecha_toma',
        'com_datos_venta_id',
        'observaciones',
        'comentario',
        'estatus',
        'activo',
    ];

    
    protected $table = 'com_toma_unidad';

    protected $connection = 'autos';

     // Relación
    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'com_vendedores_id', 'id');
    }

    public function venta()
    {
        return $this->belongsTo(DatosVenta::class, 'com_datos_venta_id', 'id');
    }

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }

    protected static function newFactory(): ComTomaUnidadFactory
    {
        //return ComTomaUnidadFactory::new();
    }
}
