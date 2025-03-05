<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenesUnidades extends Model
{
    use HasFactory;
    /**
     * Campos que pueden ser alterados
     */
    protected $fillable = [
        'servicio',
        'utilidad_servicio',
        'hyp',
        'utilidad_hyp',
        'nuevos',
        'utilidad_nuevos',
        'flotillas',
        'utilidad_flotillas',
        'seminuevos',
        'utilidad_seminuevos',
        'fecha',
        'sucursales_id'
    ];

    public $timestamps = false;
    /**
     * Nombre de la tabla
     */
    protected $connection = 'dashboard1';
    protected $table = 'ordenes_unidades';
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
