<?php

namespace Modules\Ucoip\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Ucoip\Models\PagoProgramado;
use Modules\Ucoip\Models\Servicio;

class PagoProgramadoService
{
    public function generarParaServicio(Servicio $servicio, $mesesHaciaFuturo = 12)
    {
        $periodicidad = (int) $servicio->periodicidad;
        // Si es pago único
        if (!$periodicidad || $periodicidad == 0) {
            $this->crearPago($servicio, Carbon::parse($servicio->fecha_inicio));
            return;
        }

        $fechaInicio = Carbon::parse($servicio->fecha_inicio);
        $fechaLimiteSistema = now()->copy()->addMonths($mesesHaciaFuturo);
        // Si tiene fecha_fin, usamos la menor
        $fechaFin = $servicio->fecha_fin  ? Carbon::parse($servicio->fecha_fin) : $fechaLimiteSistema;
        $fechaActual = $fechaInicio->copy();

        while ($fechaActual <= $fechaFin) {
            $this->crearPago($servicio, $fechaActual);
            // Avanzar según periodicidad
            $fechaActual->addMonths($periodicidad);
        }
    }

    private function crearPago($servicio, $fecha)
    {
        $existe = PagoProgramado::where('servicio_id', $servicio->id)
            ->whereDate('fecha_programada', $fecha)
            ->exists();

        if (!$existe) {

            $estado = $fecha < now() ? 0 : 1;

            PagoProgramado::create([
                'servicio_id' => $servicio->id,
                'fecha_programada' => $fecha,
                'fecha_limite' => $fecha,
                'importe' => $servicio->costo_base,
                'estado' => $estado
            ]);
        }
    }

    public function getPagos($anio){
         $pagos = DB::table('ucoip_pagos_programados as p')
        ->join('ucoip_servicios as s', 's.id', '=', 'p.servicio_id')
        ->join('com_proveedores as prov', 'prov.id', '=', 's.proveedor_id')
        ->join('ucoip_cat_servicio as ucs', 'ucs.id', '=', 's.tipo_servicio_id')
        ->select(
            's.id',
            'prov.nombre as proveedor',
            'ucs.nombre as tipo',
            's.nombre',
            DB::raw('MONTH(p.fecha_programada) as mes'),
            'p.estado',
            'p.importe'
        )
        ->whereYear('p.fecha_programada', $anio)
        ->get();

        $tabla = [];

        foreach ($pagos as $p) {
            if (!isset($tabla[$p->id])) {
                $tabla[$p->id] = [
                    'proveedor' => $p->proveedor,
                    'tipo' => $p->tipo,
                    'servicio' => $p->nombre,
                    'meses' => array_fill(1, 12, null)
                ];
            }

            $tabla[$p->id]['meses'][$p->mes] = [
                'estado' => $p->estado,
                'importe' => $p->importe
            ];
        }

        return array_values($tabla);

    }
   
}

