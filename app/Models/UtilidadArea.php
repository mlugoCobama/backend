<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilidadArea extends Model
{
    use HasFactory;
    /**
     * Campos que pueden ser alterados
     */
    protected $filleable = [
        'nuevos',
        'flotillas',
        'seminuevos',
        'servicio',
        'refacciones',
        'hyp',
        'area_comercial',
        'area_postventa',
        'fecha',
    ];

    public $timestamps = false;
    /**
     * Nombre de la tabla
     */
    protected $table = 'utilidad_area';
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
