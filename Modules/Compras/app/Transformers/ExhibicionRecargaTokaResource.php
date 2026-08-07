<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExhibicionRecargaTokaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
        'id'  => $this->id,
        'idAsignacion'   => $this->id_asignacion,
        'idExhibicion'   => $this->id_dispersion ?? null,
        'idSolicitud'    => $this->id_solicitud,
        'numeroExhibicion' => $this->numeroExhibicion ?? null,

        'eco' => $this->eco,
        'marca_vehiculo'  => $this->marca_vehiculo,
        'submarca'   => $this->submarca,
        'no_serie'    => $this->no_serie,
        'numTarjetaToka'    => $this->num_tarjeta_toka ?? 'No Disponible',
        'distanciaRecorrida'    => ($this->distancia_recorrida ?? 0) / 1000,
        'placas'  => $this->placas,
        'modelo'  => $this->modelo,
        'tipo'    => $this->tipo,

        'saldoMesAnterior'    => $this->saldoMesAnterior ?? 0,
        'saldoMesActual'  => $this->saldoMesActual ?? 0,
        'numAbonosMesAnterior'    => $this->numAbonosMesAnterior ?? 0,
        'numAbonosMesActual'  => $this->numAbonosMesActual ?? 0,
        'ventasLitros'    => $this->ventas_litros ?? 0,

        'saldoSolicitado' => $this->monto_solicitado ?? 0,
        'saldoAutorizado' => $this->saldoAutorizado ?? 0,
        'saldoActual'     => $this->saldoActual ?? 0,
        'montoDispersado' => $this->montoDispersadoExhibicion ?? 0,
        'fechaDispersion' => $this->fechaDispersion ?? null,

        'estatusExhibicion' => $this->estatus_exibicion ?? null,
        'guardada'   => (bool) $this->guardada ?? false,
        'notificada' => (bool) $this->notificada ?? false,
        'dispersada' => (bool) $this->dispersada ?? false,
    ];
    }
}
