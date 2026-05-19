<?php

namespace Modules\Ucoip\Services;

use Modules\Ucoip\Models\DetalleResguardo;
use Illuminate\Support\Facades\DB;
use Modules\Ucoip\Models\Resguardo;

class ResguardosService
{
    /**
     * Crear un resguardo con sus detalles
     *
     * @param mixed $data Datos del resguardo
     * @param mixed $detalles Lista de detalles
     * @return Resguardo
     */
    public function crearResguardoConDetalles(array $data, array $detalles)
    {
        return DB::transaction(function () use ($data, $detalles) {
            $resguardo = Resguardo::create([
                'usuario_asignado' => $data['usuario_asignado'],
                'fecha_inicio'     => $data['fecha_inicio'],
                'fecha_fin'        => $data['fecha_fin'],
                'empresa'        => $data['empresa'],
                'comentarios'      => $data['comentarios'] ?? null,
                'admin_rt'         => $data['admin_rt'],
            ]);

            foreach ($detalles as $detalle) {
                DetalleResguardo::create([
                    'resguardo_id'    => $resguardo->id,
                    'hardware_id'     => $detalle['hardware_id'],
                    'fecha_entrega'   => $detalle['fecha_entrega'],
                    'fecha_devolucion'=> $detalle['fecha_devolucion'] ?? null,
                    'observaciones'   => $detalle['observaciones'] ?? null,
                    'caracteristicas' => $detalle['caracteristicas'] ?? null,
                ]);
            }

            return $resguardo->load('detalles');
        });
    }

    public function storeResguardo( $data ){
        $resguardo                      = new Resguardo();
        $resguardo->id_usuario_asignado    = $data['id_usuario'];
        $resguardo->id_empresa    = $data['id_empresa'];
        $resguardo->fecha_inicio        = now();
        $resguardo->fecha_fin           = null;
        $resguardo->comentarios         = $data['comentarios'] ?? null;
        $resguardo->admin_rt            = $data['admin_rt'] ?? null;
        $resguardo->save();
        return $resguardo;
    }

    public function storeDetalle( $idHardware, $detalle, $idResguardo ){

        $detalle =  new DetalleResguardo();
        $detalle->ucoip_resguardo_ucoip_id      = $idResguardo;
        $detalle->ucoip_hardware_id       = $idHardware;
        $detalle->fecha_entrega     = now();
        $detalle->fecha_devolucion  = $detalle['fecha_devolucion'] ?? null;
        $detalle->observaciones     = $detalle['observaciones'] ?? null;
        $detalle->caracteristicas   = $detalle['caracteristicas'] ?? null;
        $detalle->save();

        
    }
}
