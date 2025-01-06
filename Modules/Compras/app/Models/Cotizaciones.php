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
    protected $table = 'cotizaciones';
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
     * Una cotizacion pertenece a una solicitud de compra
     */
    public function SolicitudCompra(){
        $this->belongsTo(SolicitudesCompra::class);
    }


    /**
     * Una cotizacion pertenece a un detalle de solicitud
     */
    // public function DetalleSolicitud(){
    //     $this->belongsTo(DetalleSolicitud::class);
    // }


    /**
     * Una cotizacion pertenece a una orden de compra
     */
    public function OrdenCompra(){
        $this->belongsTo(OrdenCompra::class);
    }

    /**
     * Una cotizacion pertenece a un proveedor
     */
    // public function Proveedor(){
    //     $this->belongsTo(proveedores::class);
    // }

    /**
     * Una cotizacion tiene varias cotizaciones_proveedor
     */

     public function CotizacionesProveedor(){
        $this->hasMany(CotizacionesProveedores::class);
     }
}
