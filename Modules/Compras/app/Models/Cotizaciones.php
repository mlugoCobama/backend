<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\CotizacionesFactory;

class Cotizaciones extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [

        'fecha',
        'folio',
        'consideraciones',
        'solicitudes_compra_id'
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'com_cotizaciones';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
    * Una cotizacion tiene varios docmuentos
    *                   ó
    * Una cotizacion tiene un documento
    */
    // public function DocsCotizacion() {
    //     $this->hasMany(DocumentosCotizaciones::class);
    // }

    /**
     * Una cotización pertenece a una solicitud de compra
     */
    public function SolicitudCompra(){
       return $this->belongsTo(SolicitudesCompra::class,'solicitudes_compra_id', 'id' );
    }


    /**
     * Una cotización pertenece a una orden de compra
     */
    public function orden_compra(){
        return $this->belongsTo(OrdenCompra::class, 'cotizaciones_id', 'id');
    }

    /**
     * Una cotización pertenece a un proveedor
     */
    // public function Proveedor(){
    //     $this->belongsTo(proveedores::class);
    // }

    /**
     * Una cotización tiene varias cotizaciones_proveedor
     */

     public function CotizacionesProveedor(){
        return $this->hasMany(CotizacionesProveedores::class);
     }
}
