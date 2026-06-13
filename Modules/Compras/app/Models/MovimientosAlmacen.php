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
        'com_id_entrega_tecnico'
    ];

    protected $table = 'com_movimientos_almacen';

    protected static function newFactory(): MovimientosAlmacenFactory
    {
        //return MovimientosAlmacenFactory::new();
    }

    public function entregaTecnico(){
        return $this->hasMany(EntregaTecnicos::class, 'com_id_entrega_tecnico', 'id');
    }
}
