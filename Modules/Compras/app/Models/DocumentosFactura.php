<?php

namespace Modules\Compras\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Compras\Database\Factories\DocumentosFacturaFactory;

class DocumentosFactura extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tipo_documento',
        'xml',
        'representacion_impresa',
        'fecha',
        'com_documentos_ordenes_compra_id'
    ];

    /**
     * Nombre de la tabla
     */
    protected $table = 'com_docs_factura';

    /**
     * Un documento de compra tiene un orden de compra
     */
    public function DocumentoOrdenCompra() {
       return $this->belongsTo(DocumentosOrdenesCompra::class);
    }
}
