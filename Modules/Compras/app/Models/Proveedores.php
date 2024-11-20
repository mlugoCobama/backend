<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\ProveedoresFactory;

class proveedores extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'contacto',
        'telefono',
        'localidad',
        'condiciones',
        'servicios'
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'proveedores';
    /**
     * Función para obtener los datos activos
     */
    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }

    protected static function newFactory(): ProveedoresFactory
    {
        //return ProveedoresFactory::new();
    }
}
