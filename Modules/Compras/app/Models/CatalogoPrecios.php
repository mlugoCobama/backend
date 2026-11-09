<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Compras\Database\Factories\CatalogoPreciosFactory;

class CatalogoPrecios extends Model
{
    use HasFactory;

    protected $table = 'com_catalogo_precios';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'com_productos_id',
        'com_proveedores_id',
        'precio',
        'dias_entrega',
        'ultima_actualizacion'
    ];
}
