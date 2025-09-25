<?php

namespace Modules\Capacitaciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Capacitaciones\Database\Factories\ModulosSubmodulosFactory;
use Modules\Capacitaciones\Transformers\FuncionesAsResource;

class ModulosSubmodulos extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'catalogos_submodulos_as_id',
        'catalogo_modulos_as_id',
        'permiso'
    ];
     /**
     * Nombre de la tabla
     */
    protected $table = 'cap_modulos_submodulos_as';
    /**
     * Relaciones
     */
    /**
     * Un modulo-submodulo tiene un modulo
     */
    // public function Modulo(){
    //     return $this->belongsTo(CatalogoModulosAs::class, 'id', 'catalogo_modulos_as_id' );
    // }
    public function Modulo(){
    return $this->belongsTo(CatalogoModulosAs::class, 'catalogo_modulos_as_id', 'id');
    }


    /**
     * Un modulo-submodulo tiene un submodulo
     */
    // public function Submodulo(){
    //     return $this->belongsTo(CatalogoSubmodulosAs::class,'id', 'catalogos_submodulos_as_id');
    // }
    public function Submodulo(){
        return $this->belongsTo(CatalogoSubmodulosAs::class, 'catalogos_submodulos_as_id', 'id');
    }
    
    public function funciones()
    {
        return $this->hasMany(CatalogoFuncionesAs::class, 'modulo_submodulos_as_id', 'id');
    }




    protected static function newFactory(): ModulosSubmodulosFactory
    {
        //return ModulosSubmodulosFactory::new();
    }
}
