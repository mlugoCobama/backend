<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatEmpresas extends Model
{
    use HasFactory;
    /**
     * Campos que pueden ser alterados
     */
    protected $filleable = [
        'nombre', 'num_intercompania'
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'cat_empresas';
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
     * Una empresa puede tener varios datos
     */
    public function Datos() {
        $this->hasMany(DatosAcumulados::class);
    }
    /**
     * Una empresa puede tener varias balanzas
     */
    public function Balanza() {
        $this->hasMany(Balanzas::class);
    }
}
