<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\DocumentosOrdenesCompraFactory;

class DocumentosOrdenesCompra extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fecha',
        'ruta_xml_factura',
        'ruta_pdf_factura',
        'comprobante_pago',
        'orden_compra_id',
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
     * Una documento de compra tiene un oreden de compra
     */
    public function OrdenesCompra() {
        $this->belongsTo(OrdenCompra::class);
    }
    
}
