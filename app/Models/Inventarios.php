<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventarios extends Model
{
    use HasFactory;
    /**
     * Campos que pueden ser alterados
     */
    protected $filleable = [
        'nuevos',
        'refacciones',
        'seminuevos',
        'inv_nuevo_101',
        'inv_nuevo_201',
        'inv_nuevo_301',
        'inv_nuevo_401',
        'inv_semi_101',
        'inv_semi_201',
        'inv_semi_301',
        'inv_semi_401',
        'fecha',
        'sucursales_id'
    ];
    public $timestamps = false;
    /**
     * Nombre de la tabla
     */
    protected $table = 'inventarios';
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
