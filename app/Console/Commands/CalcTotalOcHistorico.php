<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalcTotalOcHistorico extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calc-total-oc-historico';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calcular totales de ordenes de compra historico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
       $ordenes = DB::table('com_orden_compra as oc')
        ->join('com_cotizaciones as c', 'oc.cotizaciones_id', '=', 'c.id')
        ->join('com_cotizaciones_proveedores as cp', function($join) {
            $join->on('c.id', '=', 'cp.cotizaciones_id')
                ->where('cp.seleccionado', 1);
        })
        ->join('com_detalle_solicitud as ds', function($join) {
            $join->on('ds.solicitudes_compra_id', '=', 'c.solicitudes_compra_id')
                ->where('ds.confirmado', 1);
        })
        ->join('com_detalles_cotizacion as dc', function($join) {
            $join->on('dc.detalle_solicitud_id', '=', 'ds.id')
                ->on('dc.cotizaciones_proveedores_proveedores_id', '=', 'cp.id');
        })
        ->select('oc.id')
        ->selectRaw('SUM(dc.importe_unitario * ds.cantidad) as total_orden')
        ->groupBy('oc.id')
        ->get();

    foreach ($ordenes as $orden) {
        DB::table('com_orden_compra')
            ->where('id', $orden->id)
            ->update(['total_orden' => ($orden->total_orden * 1.16)]);
    }

    }
}
