<?php

namespace Modules\Macro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Macro\Database\Factories\TecnicoFactory;

class Tecnico extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'apellidos',
        'tipo',
        'intercompania',
        'activo',
        
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'mcr_tecnicos';   
    
    /**
     * Función para obtener los datos activos
     */
    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }

    protected static function newFactory(): TecnicoFactory
    {
        //return TecnicoFactory::new();
    }
}
