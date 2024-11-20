<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubDivisiones extends Model
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
    protected $table = 'subdivisiones';
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
     * Varias subdivisiones pertener a una division
     */
    public function Division() {
        $this->belongsTo(Divisiones::class);
    }
    /**
     * Una subdivision puede tener mas de una entidad
     */
    public function Entidades() {
        $this->hasMany(Entidades::class);
    }
}
