<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\MovimientosAlmacenFactory;

class MovimientosAlmacen extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'cantidad',
        'tipo',
        'observaciones',
        'fecha',
        'com_almacen_id',
        'id_usuario',
        'id_usuario_entrega',
        'com_id_entrega_tecnico',
        'com_almacen_producto_id'
    ];

    protected $table = 'com_movimientos_almacen';

    protected static function newFactory(): MovimientosAlmacenFactory
    {
        //return MovimientosAlmacenFactory::new();
    }

    /**
     * Un movimiento pertenece a una entrega
     */
    public function entregaTecnico(){
        return $this->belongsTo(EntregaTecnicos::class, 'com_id_entrega_tecnico', 'id');
    }

    /**
     * Un movimiento pertenece a una entrada de almacen
     */
    public function entrada(){
        return $this->belongsTo(DetalleEntrega::class, 'com_almacen_id', 'id' );
    }

    public function almacenProducto(){
        return $this->belongsTo(AlmacenProducto::class, 'com_almacen_producto_id', 'id');
    }
}
