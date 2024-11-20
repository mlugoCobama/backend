<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursales extends Model
{
    use HasFactory;
    /**
     * Campos que pueden ser alterados
     */
    protected $filleable = [
        'nombre', 'estatus'
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'sucursales';
    /**
     * Función para obtener los datos activos
     */
    public function scopeActive ($query) {
        return $query->where('estatus', 1);
    }
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     *
     */
    public function DatosGenerales() {
        $this->hasMany(DatosGenerales::class);
    }
    /**
     *
     */
    public function CostosFinancierosPrestamos() {
        $this->hasMany(CostosFinancierosPrestamos::class);
    }
    /**
     *
     */
    public function Complementos() {
        $this->hasMany(Complementos::class);
    }
    /**
     *
     */
    public function OrdenesUnidades() {
        $this->hasMany(OrdenesUnidades::class);
    }
    /**
     *
     */
    public function UtilidadArea() {
        $this->hasMany(UtilidadArea::class);
    }
    /**
     *
     */
    public function Inventarios() {
        $this->hasMany(Inventarios::class);
    }
}
