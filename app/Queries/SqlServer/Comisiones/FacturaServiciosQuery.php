<?php

namespace App\Queries\SqlServer\Comisiones;

use Illuminate\Support\Facades\DB;

class FacturaServiciosQuery
{
    public function queryFacturasServicios($connection, $fechaInicio, $fechaFin)
    {
        $facturas = $connection->table('Se_FacturaServicios as FS')
            ->join('Se_OrdenServicio as OS', 'OS.orse_folio', '=', 'FS.fase_orse_folio')
            ->join('empleados as E', 'OS.orse_ases_clave', '=', 'E.empl_clave')
            ->where('FS.fase_cancelado', 0)
            ->whereBetween('FS.fase_fecha', [$fechaInicio, $fechaFin])
            ->select(
                'FS.fase_orse_folio',
                'FS.fase_folio',
                'FS.fase_fecha',
                'FS.fase_razonsocial',
                'FS.fase_subtotal',
                'FS.fase_iva',
                'FS.fase_total',
                'OS.orse_ases_clave as vendedor_clave',
                'E.empl_agen_idagencia AS id_agencia'
            )
            ->get();

        if ($facturas->isEmpty()) return collect([]);

        $detalles = $connection->table('Se_RefacXOrden as RXO')
            ->join('Re_Productos as RP', 'RXO.dere_prod_clave', '=', 'RP.prod_clave')
            ->whereIn('RXO.dere_orse_folio', $facturas->pluck('fase_orse_folio'))
            ->select(
                'RXO.dere_orse_folio',
                'RXO.dere_prod_clave',
                'RXO.dere_cantidad',
                'RXO.dere_precio',
                'RXO.dere_preciolista',
                'RP.prod_descripcion1'
            )
            ->get()
            ->groupBy('dere_orse_folio');

        return $facturas->map(function ($factura) use ($detalles) {
            $factura->detalles = $detalles->get($factura->fase_orse_folio, collect([]))->values();
            return $factura;
        });
    }
}
