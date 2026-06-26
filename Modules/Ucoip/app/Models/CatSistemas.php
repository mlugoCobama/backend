<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\CatSistemasFactory;

class CatSistemas extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre'
    ];

    protected $table = 'ucoip_cat_sistemas';

    protected static function newFactory(): CatSistemasFactory
    {
        //return CatSistemasFactory::new();
    }
}
