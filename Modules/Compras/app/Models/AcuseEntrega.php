<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\AcuseEntregaFactory;

class AcuseEntrega extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ruta',
        'comentario',
        'fecha',
        'orden_compra_id',
    ];

    protected $table = 'com_acuses_entrega';

    public function ordenCompra()
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_compra_id', 'id');
    }



    protected static function newFactory(): AcuseEntregaFactory
    {
        //return AcuseEntregaFactory::new();
    }
}
