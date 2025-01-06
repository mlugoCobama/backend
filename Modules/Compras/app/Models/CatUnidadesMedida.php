<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\CatUnidadesMedidaFactory;

class CatUnidadesMedida extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): CatUnidadesMedidaFactory
    {
        //return CatUnidadesMedidaFactory::new();
    }
}
