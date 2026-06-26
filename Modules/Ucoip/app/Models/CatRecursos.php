<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\CatRecursosFactory;

class CatRecursos extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre'
    ];

    protected $table = 'ucoip_cat_recursos';

    protected static function newFactory(): CatRecursosFactory
    {
        //return CatRecursosFactory::new();
    }
}
