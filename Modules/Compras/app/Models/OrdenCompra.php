<?php

namespace Modules\Compras\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\OrdenCompraFactory;

class OrdenCompra extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'folio_oc',
        'fecha',
        'entrega',
        'observaciones',
        'razon_cancelacion',
        'estatus',
        'cotizaciones_id',
        'modo_pago',
        'surtido_solcitado',
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'com_orden_compra';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Una orden de compra tiene una cotización
     */
    public function cotizacion() {
        return $this->belongsTo(Cotizaciones::class, 'cotizaciones_id', 'id');
    }
    /**
     * Una orden de compra pertenece a un documento de orden de compra
     */
    public function documentos() {
       return $this->hasMany(DocumentosOrdenesCompra::class);
    }

    public function acusesEntrega()
    {
        return $this->hasMany( AcuseEntrega::class, 'orden_compra_id', 'id');
    }


    
}
