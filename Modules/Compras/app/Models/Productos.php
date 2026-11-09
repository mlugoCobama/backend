<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Compras\Database\Factories\ProductosFactory;

class Productos extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'com_subcategoria_id',
        'clave_interna',
        'cant_default',
        'com_cat_unidad_medida_id',
        'descripcion_planta',
        'descripcion_proveedor',
        'observaciones',
        'activo'
    ];

    protected $table = 'com_productos';

    /**
     * --------------------------------------
     * Relaciones
     * ----------------------------------------
     */

    /**
     * Un producto pertence a una subcategoria
     */
    public function subCategoria(){
        return $this->belongsTo(CatSubcategorias::class, 'com_subcategoria_id', 'id');
    }

    /**
     * Un producto tiene una unidad de medida
     */
    public function unidadMedida(){
        return $this->belongsTo(CatUnidadesMedidas::class, 'com_cat_unidad_medida_id', 'id');
    }

    /**
     *  ----------------------------------------
     * Scopes
     * ------------------------------------------
     */

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }

}
