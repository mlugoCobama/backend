<?php

namespace Modules\ParqueVehicular\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ParqueVehicular\Database\Factories\EventosProvOrdenFactory;

class EventosProvOrden extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pv_ordenes_proveedores_id',
        'inicio_evento',
        'fin_evento',
        'observaciones',
        'pv_cat_eventos_id'
    ];

    protected $table = 'pv_eventos_orden_servicio';

    protected static function newFactory(): EventosProvOrdenFactory
    {
        //return EventosProvOrdenFactory::new();
    }
}
