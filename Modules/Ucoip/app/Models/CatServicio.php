<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Ucoip\Database\Factories\CatServicioFactory;

class CatServicio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'descripcion', 
        'activo'
    ];

    protected $table = 'ucoip_cat_servicio';

    /**
     * relaciones
     */


    /**
     * scopes
     */

    public function scopeActive ($query) {
        return $query->where('activo', 1);
    }

    // protected static function newFactory(): CatServicioFactory
    // {
    //     // return CatServicioFactory::new();
    // }
}
