<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Compras\Database\Factories\AlmacenProductoFactory;

class AlmacenProducto extends Model
{
    use HasFactory;

    protected $table = 'com_almacen_productos';

    protected $fillable = [
        'stock',
        'stock_minimo',
        'stock_maximo',
        'almacen_empresa',
        'com_productos_id',
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'stock_maximo' => 'decimal:2',
    ];

    /**
     * Relación con el producto.
     * Un registro de almacén pertenece a un producto.
     */
    public function producto()
    {
        return $this->belongsTo(Productos::class, 'com_productos_id', 'id');
    }
}
