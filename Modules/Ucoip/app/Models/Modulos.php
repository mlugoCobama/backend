<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\ModulosFactory;

class Modulos extends Model
{
    use HasFactory;
    /**
     * Campos que pueden ser alterados
     */
    protected $fillable  = [
        'nombre',
        'descripcion',
        'activo',
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'ucoip_modulos';

    public $timestamps = true;
    /**
     * Función para obtener los datos activos
     */
    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }
}
