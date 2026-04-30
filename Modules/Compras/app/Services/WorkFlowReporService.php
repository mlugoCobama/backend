<?php

namespace Modules\Compras\Services;

use App\Models\LogEventos;
use Carbon\Carbon;
use Modules\Compras\Models\SolicitudesCompra;
use App\Enums\EstatusSolicitud;

class WorkFlowReporService{
    
public function getSolicitudesComprasTipo($tipo, $empresa = null){
    return SolicitudesCompra::active()
    ->where('tipo' , $tipo)
    ->when($empresa, function ($query) use ($empresa) {
            $query->where('empresa', $empresa);
        })
    ->get(['id', 'folio', 'estatus', 'empresa',  'created_at'])
    ->keyBy('id');
}

public function getLogsEventos($tipo = 1)
    {
        // IDs por tipo
        $solcitudes = $this->getSolicitudesComprasTipo($tipo);
        $ids = $solcitudes->keys();

        if ($ids->isEmpty()) {
            return [];
        }

        // Logs
        $logs = LogEventos::where('table_name', 'com_solicitudes_compra')
            ->whereIn('record_id', $ids)
            ->orderBy('record_id')
            ->orderBy('created_at')
            ->get();

        //  Agrupar
        $logsAgrupados = $logs->groupBy('record_id');

        //  Mapa de estatus
        $mapEstados = [
            1 => 'creado',
            2 => 'solictado',
            3 => 'cotizacion',
            4 => 'cancelado',
            5 => 'orden_compra',
            6 => 'autorizada',
            8 => 'en_surtido',
            9 => 'entregada',
            10 => 'facturado',
            11 => 'solicitado_pago',
            12 => 'pagada',
            13 => 'carga_complemento',
            14 => 'finalizada'
        ];

        $resultado = [];

        foreach ($logsAgrupados as $recordId => $eventos) {

            $fechas = [];

            foreach ($eventos as $evento) {
          
                $newValues = $evento->new_values;

                if (!is_array($newValues) || !isset($newValues['estatus'])) {
                    continue;
                }

                $estatus = (int) $newValues['estatus'];

                if (!isset($mapEstados[$estatus])) continue;

                $key = $mapEstados[$estatus];

                if (!isset($fechas[$key])) {
                    $fechas[$key] = $evento->created_at;
                }
            }

            $fCreado = $solcitudes[$recordId]->created_at ?? null;
            $fSolicictud = $fechas['solictado'] ?? null;

            $fCotizacion =  $fechas['cotizacion'] ?? null;
            $fCancelacion =  $fechas['cancelado'] ?? null;
            $fOrdenCompra =  $fechas['orden_compra'] ?? null;
            $fautorizada =  $fechas['autorizada'] ?? null;
            $fSurtido =  $fechas['en_surtido'] ?? null;

            $fEntrega =  $fechas['entregada'] ?? null;
            $fFacturado =  $fechas['facturado'] ?? null;

            $fSolictadoPago =  $fechas['solicitado_pago'] ?? null;
            $fPago =  $fechas['pagada'] ?? null;
            $fCargaComplemento =  $fechas['carga_complemento'] ?? null;
            $fFinalizacion =  $fechas['finalizada'] ?? null;
            
            $resultado[] = [
                
                'folio' => $solcitudes[$recordId]->folio ?? null,
                'ultimoEstado' => EstatusSolicitud::getLabel($solcitudes[$recordId]->estatus),
                'creado' => $fCreado->format('d/m/Y H:i:s'),
                'esp_auto_planta' => $this->diffFormateado($fCreado, $fSolicictud ),
                'fecha_solicitado' => $this->formatoFecha($fSolicictud),
                
                'fecha_cotizacion' => $this->formatoFecha($fCotizacion),
                'solicitado' => $this->diffFormateado($fSolicictud, $fCotizacion ),

                'fecha_orden_compra' => $this->formatoFecha($fOrdenCompra),
                'cotizacion' => $this->diffFormateado($fCotizacion,$fOrdenCompra),

                // 'fecha_orden_compra' => $this->formatoFecha($fOrdenCompra),
                'fecha_en_surtido' => $this->formatoFecha($fSurtido),
                'orden_compra' => $this->diffFormateado($fOrdenCompra,$fSurtido),

                'fecha_facturado' => $this->formatoFecha($fFacturado),
                'facturado' => $this->diffFormateado($fOrdenCompra,$fFacturado),

                'fecha_solicidtado_a_pago' => $this->formatoFecha($fSolictadoPago),
                'fsurtido' => $this->diffFormateado($fSurtido,$fSolictadoPago),

                'fecha_pagado' => $this->formatoFecha($fPago),
                'pagado' => $this->diffFormateado($fSolictadoPago,$fPago),

                'fecha_complemto_pago' => $this->formatoFecha($fCargaComplemento),
                'complemento_pago' => $this->diffFormateado($fPago, $fCargaComplemento ),

                'finalizado'=> $this->formatoFecha($fFinalizacion),
                'tiempo_total' => $this->diffFormateado($fCreado,$fFinalizacion),

                'cancelado'=> $this->formatoFecha($fCancelacion),
                'tiempo_cancelacion' => $this->diffFormateado($fCreado,$fCancelacion),
                
                'fecha_entrega' => $this->formatoFecha($fEntrega),
                'tiempo_entrega' => $this->diffFormateado($fSurtido,$fEntrega),
            
            ];
        }

        return $resultado;
    }

    /**
     * Calculo de diferencia en horas
     */
    private function diff($inicio, $fin)
    {
        if (!$inicio || !$fin) return null;

        return Carbon::parse($inicio)->diffInHours($fin);
    }

    /**
     * Calculo de diferencia de fechas
     */
    private function diffFormateado($inicio, $fin)
    {
        if (!$inicio || !$fin) return null;

        $totalSegundos = Carbon::parse($inicio)->diffInSeconds($fin, true);

        $dias = floor($totalSegundos / 86400);
        // $horas = floor(($totalSegundos % 86400) / 3600);
        // $minutos = floor(($totalSegundos % 3600) / 60);
        // $segundos = $totalSegundos % 60;

        return $dias;
        // return "{$dias} días, {$horas} horas, {$minutos} minutos, {$segundos} segundos";
    }

    /**
     * Formateo de fechas
     */
    public function formatoFecha($fecha, $conHora = false)
    {
        if(empty($fecha)){
            return null;
        }
        $carbon = Carbon::parse($fecha);

        return $conHora
            ? $carbon->format('d/m/Y H:i:s')
            : $carbon->format('d/m/Y');
    }
}
