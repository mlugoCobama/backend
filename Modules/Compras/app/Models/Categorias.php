<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\CategoriasFactory;

class Categorias extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    protected $table = 'com_categorias';

    public function productos(){
        return $this->hasMany(ProveedorProducto::class, 'categoria_id',  'id');
    }

    protected static function newFactory(): CategoriasFactory
    {
        //return CategoriasFactory::new();
    }
}
