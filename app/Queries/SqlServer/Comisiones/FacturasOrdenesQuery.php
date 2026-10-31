<?php

namespace App\Queries\SqlServer\Comisiones;

use Illuminate\Support\Facades\DB;

class FacturasOrdenesQuery
{
    public function getFacturas($connection, $fechaInicio, $fechaFin)
    {
        $facturas = $connection->table('Re_EncabezadoPedido as REP')
            ->join('empleados as E', 'REP.pedi_empl_clave', '=', 'E.empl_clave')
            ->where('REP.pedi_status', 'FA')
            ->whereBetween('REP.pedi_fechafactura', [$fechaInicio, $fechaFin])
            ->select(
                'REP.pedi_numeropedido',
                'REP.pedi_numerofactura',
                'REP.pedi_fechafactura',
                'REP.pedi_empl_clave',
                'REP.pedi_razonfactura',
                'REP.pedi_ivafactura',
                'REP.pedi_importetotal',
                'E.empl_nombre',
                'E.empl_apellidopaterno',
                'E.empl_apellidomaterno',
                'E.empl_agen_idagencia AS id_agencia'
            )
            ->get();

        if ($facturas->isEmpty()) return collect([]);

        $detalles = $connection->table('Re_DetallePedido as RDP')
            ->join('Re_Productos as RP', 'RDP.depe_prod_claveproducto', '=', 'RP.prod_clave')
            ->whereIn('RDP.depe_pedi_numeropedido', $facturas->pluck('pedi_numeropedido'))
            ->get()
            ->groupBy('depe_pedi_numeropedido');

        return $facturas->map(function ($factura) use ($detalles) {
            $factura->detalles = $detalles->get($factura->pedi_numeropedido, collect([]))->values();
            return $factura;
        });
    }
}
