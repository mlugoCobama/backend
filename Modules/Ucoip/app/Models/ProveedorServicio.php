<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Ucoip\Database\Factories\ProveedorServicioFactory;

class ProveedorServicio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): ProveedorServicioFactory
    // {
    //     // return ProveedorServicioFactory::new();
    // }
}
