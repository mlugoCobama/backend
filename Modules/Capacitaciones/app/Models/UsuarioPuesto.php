<?php

namespace Modules\Capacitaciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Capacitaciones\Database\Factories\UsuarioPuestoFactory;

class UsuarioPuesto extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id_usuario',
        'id_puesto',
        'activo',
    ];



    /**
     * Nombre de la tabla
     */
    protected $table = 'usuario_puesto';

    /**
     * Relaciones
     */
    public function puesto(){
        return $this->belongsTo(CatalogoPuestos::class, 'id_puesto', 'id');
    }

    protected static function newFactory(): UsuarioPuestoFactory
    {
        //return UsuarioPuestoFactory::new();
    }

    /**
     * Función para obtener los datos activos
     */
    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }
}
