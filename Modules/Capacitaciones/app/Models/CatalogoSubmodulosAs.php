<?php

namespace Modules\Capacitaciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Capacitaciones\Database\Factories\CatalogoSubmodulosAsFactory;

class CatalogoSubmodulosAs extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'nombre',
        'permiso',
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'cap_cat_submodulos_as';
    /**
     * Relaciones
     */

    /**
     * Un submodulo tiene varios modulos-submodulos
     */
    public function SubmodulosModulos(){
        return $this->hasMany(ModulosSubmodulos::class,'catalogos_submodulos_as_id', 'id');
    }
    
    protected static function newFactory(): CatalogoSubmodulosAsFactory
    {
        //return CatalogoSubmodulosAsFactory::new();
    }
}
