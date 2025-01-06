<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\OrdenCompraFactory;

class OrdenCompra extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'folio_oc',
        'fecha',
        'observaciones',
        'estatus',
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'orden_compra';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Una orden de compra tiene una cotización
     */
    public function Cotizacion() {
        $this->hasOne(Cotizaciones::class);
    }
    /**
     * Una orden de compra pertenece a un documento de orden de compra
     */
    public function DocOrdenCompra() {
        $this->belongsTo(DocumentosOrdenesCompra::class);
    }
    
}
