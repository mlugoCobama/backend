<?php

namespace Modules\ParqueVehicular\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ParqueVehicular\Database\Factories\GarantiasFactory;

class Garantias extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'descripcion',
        'duracion',
        'inicio_garantia'
    ];

    protected static function newFactory(): GarantiasFactory
    {
        //return GarantiasFactory::new();
    }
}
