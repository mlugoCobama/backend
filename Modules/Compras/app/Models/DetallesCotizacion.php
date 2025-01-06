<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\DetallesCotizacionFactory;

class DetallesCotizacion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'detalle_solicitud_id',
        'importe_unitario',
        'cotizaciones_proveedores_proveedores_id',
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'detalles_cotizacion';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Un detalle de cotizacionn 
     */
    
    protected static function newFactory(): DetallesCotizacionFactory
    {
        //return DetallesCotizacionFactory::new();
    }
}
