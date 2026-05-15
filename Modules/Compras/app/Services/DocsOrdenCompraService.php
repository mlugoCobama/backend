<?php

namespace Modules\Compras\Services;

use Illuminate\Support\Facades\DB;

class DocsOrdenCompraService{

public function queryComprobantes($idOrdenCompra){
        $rutasQuery = DB::table('com_documentos_ordenes_compra')
            ->select([
                'id',
                'fecha',
                DB::raw("'factura' as tipo_documento"),
                'ruta_pdf_factura as representacion_impresa',
                'ruta_xml_factura as xml'
            ])
            ->where('orden_compra_id', $idOrdenCompra)
            ->whereNotNull('ruta_pdf_factura')
            ->where('ruta_pdf_factura', '!=', '');

        // Segundo conjunto: comprobante
        $comprobanteQuery = DB::table('com_documentos_ordenes_compra')
            ->select([
                'id',
                'fecha',
                DB::raw("'comprobante_pago' as tipo_documento"),
                'comprobante_pago as representacion_impresa',
                DB::raw("'' as xml")
            ])
            ->where('orden_compra_id', $idOrdenCompra)
            ->whereNotNull('comprobante_pago')
            ->where('comprobante_pago', '!=', '');
        // Unión de ambas consultas
        return $rutasQuery->union($comprobanteQuery)->get();
    }
}