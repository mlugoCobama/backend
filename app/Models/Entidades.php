<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entidades extends Model
{
    use HasFactory;
    /**
     * Campos que pueden ser alterados
     */
    protected $filleable = [
        'nombre', 'num_intercompañia', 'estatus'
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'entidades';
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
     * Varias entidades pertener a una subdivision
     */
    public function SubDivision() {
        $this->belongsTo(SubDivisiones::class);
    }
    /**
     * Una entidad puede tener mas de una sucursal
     */
    public function Sucursales() {
        $this->hasMany(Sucursales::class);
    }
}
