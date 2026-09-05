<?php

namespace Modules\Ucoip\Services;

use Modules\Ucoip\Models\DetalleResguardo;
use Illuminate\Support\Facades\DB;
use Modules\Ucoip\Models\GlpiUser;
use Modules\Ucoip\Models\HardwareUcoip;
use Modules\Ucoip\Models\Resguardo;
use App\Enums\EstatusActivos;
use App\Enums\EstatusAsignaciones;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Ucoip\Models\ComponenteHardware;
use Modules\Ucoip\Models\HardwarePcModel;

class ResguardosService
{
    /**
     * Crear un resguardo con sus detalles
     *
     * @param mixed $data Datos del resguardo
     * @param mixed $detalles Lista de detalles
     */
    public function crearResguardoConDetalles(array $data, array $detalles)
    {
        return DB::transaction(function () use ($data, $detalles) {
            $resguardo = Resguardo::create([
                'usuario_asignado'  => $data['usuario_asignado'],
                'fecha_inicio'      => $data['fecha_inicio'],
                'fecha_fin'         => $data['fecha_fin'],
                'empresa'           => $data['empresa'],
                'comentarios'       => $data['comentarios'] ?? null,
                'admin_rt'          => $data['admin_rt'],
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

    /**
     * Crear un resguardo sin detalles
     *
     * @param mixed $data Datos del resguardo
     */
    public function storeResguardo( $data ){
        $resguardo                      = new Resguardo();
        $resguardo->id_usuario_asignado = $data['id_usuario'];
        $resguardo->id_empresa          = $data['id_empresa'];
        $resguardo->fecha_inicio        = now();
        $resguardo->fecha_fin           = null;
        $resguardo->comentarios         = $data['comentarios'] ?? null;
        $resguardo->admin_rt            = $data['admin_rt'] ?? null;
        $resguardo->folio            = $this->generarFolio();
        $resguardo->save();
        return $resguardo;
    }

    /**
     * Genera un folio para un nuevo resguardo
     *
     * @return string
     */
    private function generarFolio()
    {
        // Obtener el último folio registrado
        $ultimoFolio = Resguardo::orderBy('id', 'desc')->first();

        if ($ultimoFolio) {
            // Extraer la parte numérica del folio
            $numero = (int) str_replace('RRT-', '', $ultimoFolio->folio);
            $nuevoNumero = $numero + 1;
        } else {
            // Si no existe ningún folio, empezamos en 1
            $nuevoNumero = 1;
        }
        // Formatear con ceros a la izquierda (ejemplo: ETI-00001)
        $nuevoFolio = 'RRT-' . str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT);

        return $nuevoFolio;
    }



    public function storeDetalle( $idHardware, $detalle, $idResguardo ){
        $detalle =  new DetalleResguardo();
        $detalle->ucoip_resguardo_ucoip_id  = $idResguardo;
        $detalle->ucoip_hardware_id         = $idHardware;
        $detalle->fecha_entrega             = now();
        $detalle->fecha_devolucion          = $detalle['fecha_devolucion'] ?? null;
        $detalle->observaciones             = $detalle['observaciones'] ?? null;
        $detalle->caracteristicas           = $detalle['caracteristicas'] ?? null;
        $detalle->save();
    }

    /**
     * Asignar recurso a hardware
     *
     * @param mixed $idHardware ID del equipo
     * @param mixed $idUcoip ID de UCOIP
     * @param mixed $idUserGlpi ID del usuario en GLPI
     */
    public function asignarRecurso($idHardware, $idUcoip, $idUserGlpi){
        $asignacion                     = new HardwareUcoip();
        $asignacion->ucoip_hardware_id  = $idHardware;
        $asignacion->ucoip_ucoip_id     = $idUcoip;
        $asignacion->glpi_user_id       = $idUserGlpi;
        $asignacion->fecha_inicio       = now();
        $asignacion->estatus            = EstatusAsignaciones::ACTIVA;
        $asignacion->save();

        $this->updateEstatusHardware($idHardware, EstatusActivos::ASIGNADA);

        return $asignacion;
    }

    /**
     * Actualizar el estatus de un hardware
     *
     * @param mixed $id Id del hardware
     * @param string $estado Nuevo estado del hardware
     */
    public function updateEstatusHardware($id, $estado){
        $hardware =  HardwarePcModel::findOrFail($id);
        if($hardware){
            $hardware->estado = $estado;
        }
        $hardware->save();
    }

    /**
     * Remover recurso asignado a hardware
     *
     * @param mixed $idAsignacion ID de la asignación
     */
    public function removerRecurso($idAsignacion){
       $asignacion                  = HardwareUcoip::find($idAsignacion);
       if($asignacion){
        $asignacion->fecha_fin      = now();
        $asignacion->estatus        = EstatusAsignaciones::FINALIZADA;
        $asignacion->save();
       }

       return $asignacion;
    }


    /**
     * Recuperar la computadora que tiene asignada un usuario
     *
     * @param mixed $id Id del usuario
     */
    public function getPcUsuario($id)
    {
        $tipoHardware = 1;
        $resguardo = HardwareUcoip::with(['hardware.tipoHardware'])
            ->where('glpi_user_id', $id)
            ->where(function ($query) {     $query->where('fecha_fin', null);   })
            ->whereHas('hardware', function ($query) use ($tipoHardware) {
                $query->where('cat_hardware_id', $tipoHardware);
            })
            ->get();
        return $resguardo;
    }

    /**
     * Asignar recurso a equipo de PC
     *
     * @param mixed $idEquipo ID del equipo de PC
     * @param mixed $idDetalleSolicitud ID del detalle de solicitud
     */
    public function asignarRecursoPc( $idEquipo, $idDetalleSolicitud){
        $detalle = DetalleSolicitud::with('cotizacionSeleccionada')->find($idDetalleSolicitud);

        $componente =  new ComponenteHardware();
        $componente->ucoip_hardware_id = $idEquipo;
        $componente->tipo = 1;
        $componente->descripcion = $detalle->descripcion ?? '';
        $componente->cantidad = 1;
        $componente->costo_unitario = $detalle->cotizacionSeleccionada->importe_unitario ?? 0;
        $componente->costo_total = ($detalle->cotizacionSeleccionada->importe_unitario  ?? 0) * 1.16;
        $componente->componente_reemplazado = '';
        $componente->fecha_instalacion = now();
        $componente->fecha_retiro = null;
        $componente->observaciones = '';
        $componente->com_detalle_solicitud_id =  $idDetalleSolicitud;
        $componente->save();

        return $detalle;

    }
}
