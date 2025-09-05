<?php

namespace Modules\Macro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Macro\Database\Factories\SalidaFactory;

class Salida extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fecha',
        'mcr_almacen_id',
        'mcr_tecnicos_id',
        'cantidad',
        'observaciones',
        'com_ordenes_compras_id'
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'mcr_salidas';

    protected static function newFactory(): SalidaFactory
    {
        //return SalidaFactory::new();
    }
}
