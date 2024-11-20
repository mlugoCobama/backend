<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NissanMesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'planta' => $this->planta,
            'estacion' => $this->estacion,
            'fecha' => $this->fecha,
            'uno' => $this->uno,
            'gasto' => $this->gasto,
            'nuevos' => $this->nuevos,
            'flotillas' => $this->flotillas,
            'refacciones' => $this->refacciones,
            'bajio' => $this->bajio,
            'intercias' => $this->intercias,
            'plan_piso' => $this->plan_piso,
            'plan_piso_interes' => $this->plan_piso_interes,
            'nrf' => $this->nrf,
            'nrf_interes' => $this->nrf_interes,
            'servicio' => $this->servicio,
            'utilidad_servicio' => $this->utilidad_servicio,
            'hyp' => $this->hyp,
            'utilidad_hyp' => $this->utilidad_hyp,
            'nuevos' => $this->nuevos,
            'utilidad_nuevos' => $this->utilidad_nuevos,
            'flotillas' => $this->flotillas,
            'utilidad_flotillas' => $this->utilidad_flotillas,
            'seminuevos' => $this->seminuevos,
            'utilidad_seminuevos' => $this->utilidad_seminuevos,
            'objetivo' => $this->objetivo,
            'cumplimiento' => $this->cumplimiento,
            'porcentaje' => $this->porcentaje,
            'bono_marca' => $this->bono_marca,
            'bonos' => $this->bonos,
            'incentivos' => $this->incentivos,
            'otros' => $this->otros,
            'descuentos' => $this->descuentos,
            'nuevos' => $this->nuevos,
            'refacciones' => $this->refacciones,
            'seminuevo' => $this->seminuevos
        ];
    }
}
