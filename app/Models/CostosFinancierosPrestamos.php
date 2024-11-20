<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostosFinancierosPrestamos extends Model
{
    use HasFactory;
    /**
     * Campos que pueden ser alterados
     */
    protected $filleable = [
        'nuevos',
        'flotillas',
        'refacciones',
        'bajio',
        'intercias',
        'plan_piso',
        'plan_piso_interes',
        'nrf',
        'nrf_interes',
        'fecha',
    ];

    public $timestamps = false;
    /**
     * Nombre de la tabla
     */
    protected $table = 'costos_financieros_prestamos';
    /**
     * Función para obtener los datos activos
     */
    public function scopeActive ($query) {
        return $query->where('estatus', 1);
    }

    public function scopeDate ($query) {
        return $query->where('fecha', 1);
    }

    public function scopeSucursal ($query) {
        return $query->where('sucursales_id', 1);
    }
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     *
     */
    public function Sucursal() {
        $this->belongsTo(Sucursales::class);
    }
}
