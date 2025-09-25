<?php

namespace Modules\Capacitaciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Capacitaciones\Database\Factories\CatalogoModulosAsFactory;
use App\Traits\Auditable;

class CatalogoModulosAs extends Model
{
    use Auditable;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'permiso'
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'cap_catalogo_modulos_as';
    
    /**
     * Relaciones
     */

    /**
     * Un modulo tiene varios modulos-submodulos
     */
    public function ModulosSubmodulos(){
        return $this->hasMany(ModulosSubmodulos::class,'catalogo_modulos_as_id', 'id');
    }

    protected static function newFactory(): CatalogoModulosAsFactory
    {
        //return CatalogoModulosAsFactory::new();
    }
}
