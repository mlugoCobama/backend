<?php

namespace Modules\Capacitaciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Capacitaciones\Database\Factories\CatalogoFuncionesAsFactory;

class CatalogoFuncionesAs extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'ruta_video',
        'permiso',
        'modulo_submodulos_as_id'
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'cap_cat_funciones_as';
    /**
     * Relaciones
     */

    /**
     * Una funcion tiene un modulo submodulo
     */
    public function ModulosSubmodulos(){
        return $this->belongsTo(ModulosSubmodulos::class, 'id', 'modulo_submodulos_as_id');
    }
    

    protected static function newFactory(): CatalogoFuncionesAsFactory
    {
        //return CatalogoFuncionesAsFactory::new();
    }
}
