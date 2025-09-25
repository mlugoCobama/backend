<?php

namespace Modules\Capacitaciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Capacitaciones\Database\Factories\CatalogoPuestosFactory;
use App\Traits\Auditable;

class CatalogoPuestos extends Model
{
    use Auditable;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'activo',
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'cap_cataologo_puestos';
    /**
     * Relaciones
     */

    public function permisosPuesto(){
        return $this->hasMany(PermissionHasPuesto::class, 'puesto_id', 'id');
    }

    protected static function newFactory(): CatalogoPuestosFactory
    {
        //return CatalogoPuestosFactory::new();
    }

    /**
     * Función para obtener los datos activos
     */
    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }
}
