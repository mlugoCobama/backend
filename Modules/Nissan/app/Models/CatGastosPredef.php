<?php

namespace Modules\Nissan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Nissan\Database\Factories\CatGastosPredefFactory;

class CatGastosPredef extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'clave_gasto', 'importe', 'agencia', 'tipo_vehiculo', 'activo'
    ];

    protected $table = 'com_cat_gasto_predef';

    protected $connection = 'autos';

    protected static function newFactory(): CatGastosPredefFactory
    {
        //return CatGastosPredefFactory::new();
    }
}
