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
    ];
    /**
     * Nombre de la tabla
     */
    protected $table = 'documentos_ordenes_compras';
    /*
     |--------------------------------------------------------------------------
     | RELACIONES DE BASE DE DATOS
     |--------------------------------------------------------------------------
     */
    /**
     * Una documento de compra tiene un oreden de compra
     */
    public function OrdenesCompra() {
        $this->hasOne(OrdenCompra::class);
    }
    
}
