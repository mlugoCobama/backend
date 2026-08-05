<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\CatCheckListMantenimientosFactory;

class CatCheckListMantenimientos extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tipo',
        'codigo_control',
        'etiqueta',
        'orden',
        'activo'
    ];

    protected $table = "ucoip_cat_checklist_mantenimiento";

    protected static function newFactory(): CatCheckListMantenimientosFactory
    {
        //return CatCheckListMantenimientosFactory::new();
    }
    /**
     * Función para obtener los datos activos
     */
    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }
}
