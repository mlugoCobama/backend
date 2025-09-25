<?php

namespace Modules\Capacitaciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Capacitaciones\Database\Factories\PuestosModulosAsFactory;

class PuestosModulosAs extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    /**
     * Nombre de la tabla
     */
    // protected $table = 'cap_cat_submodulos_as';
    /**
     * Relaciones
     */

    protected static function newFactory(): PuestosModulosAsFactory
    {
        //return PuestosModulosAsFactory::new();
    }
}
