<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\ProveedorProductoFactory;

class ProveedorProducto extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'proveedor_id',
        'categoria_id',
        'nombre',
        'descripcion',
        'unidad',
        'precio_unitario', 
        'activo'
    ];

    protected $table = 'com_proveedor_productos';

    protected static function newFactory(): ProveedorProductoFactory
    {
        //return ProveedorProductoFactory::new();
    }

    public function proveedor(){
        return $this->belongsTo(Proveedores::class, 'proveedor_id', 'id');
    }

    public function categorias(){
        return $this->belongsTo(Categorias::class, 'categoria_id', 'id');
    }
    
}
