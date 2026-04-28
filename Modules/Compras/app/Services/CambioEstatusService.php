<?php

namespace Modules\Compras\Services;

use App\Enums\EstatusOrdenCompra;
use App\Enums\EstatusSolicitud;
use App\Helpers\NotificationHelper;
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\OrdenCompra;
use Modules\Compras\Models\SolicitudesCompra;

class CambioEstatusService{

/**
 * Actualizar estatus de solicitud usando uan orden de compra como referencia
 */
public function actStatusOrdenSolicitud($idOrdenCompra, $statusOrdenCompra, $estatusSolicitud)
    {
        // Buscar la orden de compra
        $orden = OrdenCompra::find($idOrdenCompra);
        if ($orden) {
            $orden->estatus = $statusOrdenCompra;
            if ($statusOrdenCompra === EstatusOrdenCompra::EN_SURTIDO) {
                $orden->surtido_solcitado = 1;
            }
            $orden->save();
            // Buscar la cotización relacionada
            $cotizacion = Cotizaciones::find($orden->cotizaciones_id);
            if ($cotizacion) {
                // Buscar la solicitud relacionada
                $solicitud = SolicitudesCompra::find($cotizacion->solicitudes_compra_id);
                if ($solicitud) {
                    $labels = EstatusSolicitud::labels();
                    $label = $labels[$estatusSolicitud] ?? 'DESCONOCIDO';
                    $solicitud->estatus = $estatusSolicitud;
                    $solicitud->save();
                    // Notificación
                    NotificationHelper::sendNotificationEstatusChange($solicitud->id, $label);
                }
            }

            return [
                'idSolicitud' => $solicitud->id     ??  null,
                'idCotizacion' => $cotizacion->id   ??  null,
                'idOrdenCompra' => $orden->id      ??  null,
            ];
        }
    }


    /**
 * Actualizar estatus de solicitud usando una solicitud de compra como referencia
 */
public function actStatusSolicitudOrden($idSolicitud, $estatusSolicitud, $estatusOrdenCompra)
    {
        // Buscar la solicitud
        $solicitud = SolicitudesCompra::find($idSolicitud);

        if ($solicitud) {
            // Actualizar estatus de la solicitud
            $solicitud->estatus = $estatusSolicitud;
            $solicitud->save();
            $labels = EstatusSolicitud::labels();
            $label = $labels[$estatusSolicitud] ?? 'DESCONOCIDO';
            NotificationHelper::sendNotificationEstatusChange($solicitud->id, $label);
            // Buscar la cotización relacionada
            $cotizacion = Cotizaciones::where('solicitudes_compra_id', $solicitud->id)->first();
            if ($cotizacion) {
                // Buscar la orden de compra relacionada
                $orden = OrdenCompra::where('cotizaciones_id', $cotizacion->id)->first();
                if ($orden) {
                    $orden->estatus = $estatusOrdenCompra;
                    if ($estatusOrdenCompra === EstatusOrdenCompra::EN_SURTIDO) {
                        $orden->surtido_solcitado = 1;
                    }
                    $orden->save();
                }
            }

            return [
                'idSolicitud' => $solicitud->id     ??  null,
                'idCotizacion' => $cotizacion->id   ??  null,
                'idOrdenCompra' => $orden->id      ??  null,
            ];
        }
    }

    public function actStatusSolicitudToOrden($idSolicitud, $data){
        $solicitud = SolicitudesCompra::find($idSolicitud);
            if ($solicitud) {
                $labels = EstatusSolicitud::labels();
                $label = $labels[EstatusSolicitud::EN_ORDEN_COMPRA] ?? 'DESCONOCIDO';
                if(isset($data["categoria"]) && !empty($data["categoria"]) && ($solicitud->tipo != 2 && $solicitud->tipo != 3)){
                        $solicitud->com_cat_sistemas_auto_id = $data["categoria"];
                }
                $solicitud->estatus = EstatusSolicitud::EN_ORDEN_COMPRA;
                $solicitud->save();

                NotificationHelper::sendNotificationEstatusChange($solicitud->id, $label);
            }

    }


}