<?php

namespace Modules\Ucoip\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ucoip\Database\Factories\TitularesUcoipFactory;

class TitularesUcoip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ucoip_ucoip_id', 
        'nombre_titular', 
        'correo', 
        'puesto', 
        'empresa',
        'fecha_incio', 
        'fecha_fin'
    ];

    protected $table = 'ucoip_titulares_ucoip';

    protected static function newFactory(): TitularesUcoipFactory
    {
        //return TitularesUcoipFactory::new();
    }
}
