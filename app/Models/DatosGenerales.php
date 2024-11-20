<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatosGenerales extends Model
{
    use HasFactory;
    /**
     * Campos que pueden ser alterados
     */
    protected $filleable = [
        'uno',
        'gasto',
        'ventas',
        'ventas_litros',
        'utilidad_bruta',
        'personal',
        'fecha',
    ];

    public $timestamps = false;
    /**
     * Nombre de la tabla
     */
    protected $table = 'datos_generales';
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
