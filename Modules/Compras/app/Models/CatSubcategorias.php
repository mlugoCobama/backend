<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Compras\Database\Factories\CatSubcategoriasFactory;

class CatSubcategorias extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'com_categoria_id',
        'nombre',
        'descripcion'
    ];

    protected $table = 'com_subcategoria';

    /**
     * --------------------------------------
     * Relaciones
     * ----------------------------------------
     */

    /**
     * Una subcategoria pertenece a una categoria
     */
    public function categoria(){
       return $this->belongsTo(CatSistemasAuto::class, 'com_categoria_id', 'id');
    }

    /**
     * Una subcategoria si tiene muchos productos
     */
    public function productosActivos(){
       return $this->hasMany(Productos::class, 'com_subcategoria_id', 'id')->where('activo', 1);
    }
}
