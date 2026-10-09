<?php

namespace Modules\ParqueVehicular\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ParqueVehicular\Database\Factories\PartesTrabajoFactory;

class PartesTrabajo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'descripcion_parte',
        'precio_unitario',
        'cantidad',
        'no_parte',
        'pv_trabajos_orden_id'
    ];

    protected $table = 'pv_partes_trabajo';

    protected static function newFactory(): PartesTrabajoFactory
    {
        //return PartesTrabajoFactory::new();
    }
}
