<?php

namespace Modules\Compras\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\DocumentosOrdenesCompraFactory;

class DocumentosOrdenesCompra extends Model
{
    use HasFactory;
    use Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fecha',
        'ruta_xml_factura',
        'ruta_pdf_factura',
        'complemento_pago_xml',
        'complemento_pago_pdf',
        'comprobante_pago',
        'orden_compra_id',
        'sync',
        'syncned_at'
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'com_documentos_ordenes_compra';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Un documento de compra tiene un orden de compra
     */
    public function OrdenesCompra() {
        $this->belongsTo(OrdenCompra::class);
    }
    /**
     * Un documento (factura) tiene varios documentos
     */
    public function documentosFactura(){
        return $this->hasMany(DocumentosFactura::class, "com_documentos_ordenes_compra_id", "id");
    }
    
}
