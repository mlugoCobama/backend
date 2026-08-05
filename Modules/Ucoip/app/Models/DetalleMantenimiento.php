<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\DetalleMantenimientoFactory;

class DetalleMantenimiento extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'com_detalle_solicitud_id',
        'ucoip_hardware_mantenimientos_id',
        'descripcion',
        'origen',
        'no_serie_anterior',
        'no_serie_nueva',
        'cantidad',
        'costo_unitario',
        'costo_total',
        'activo',
        'observacion'
    ];

    protected  $table = 'ucoip_detalle_mantenimiento';

    protected static function newFactory(): DetalleMantenimientoFactory
    {
        //return DetalleMantenimientoFactory::new();
    }
    /**
     * Función para obtener los datos activos
     */

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }
}
