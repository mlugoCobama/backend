<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecargaTokaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'  => $this->id,
            'id_asignacion'  => $this->id_asignacion,
            'eco' => $this->eco,
            'marca_vehiculo'  =>$this->marca_vehiculo,
            'submarca'   => $this->submarca,
            'no_serie'    => $this->no_serie,
            'numTarjetaToka'    => $this->num_tarjeta_toka ?? 'No Disponible',
            'distanciaRecorrida'    => ($this->distancia_recorrida ?? 0) / 1000,
            'placas'  => $this->placas,
            'modelo'  => $this->modelo,
            'tipo'    => $this->tipo,

            'saldoMesAnterior'    => $this->saldoMesAnterior,
            'saldoMesActual'  => $this->saldoMesActual,

            'numAbonosMesAnterior'    => $this->numAbonosMesAnterior,
            'numAbonosMesActual'  => $this->numAbonosMesActual,

            'ventasLitros'    => $this->ventas_litros,
            'saldoSolicitado' => $this->monto_solicitado,

            'idSolicitud' => $this->id_solicitud, 
            'saldoActual' => $this->saldoActual ?? 0,
            // 'saldoActual' => $this-> number,
            // 'saldoDispersar'  => $this->number,
        ];
    }
}
