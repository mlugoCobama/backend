<?php

namespace Modules\Dashboard\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataMesUtilidadAreaPvResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'planta' => $this->planta,
            'estacion' => str_replace("PV ","",$this->estacion),
            'fecha' => $this->fecha,
            'area_nuevos' => $this->nuevos ?? 0,
            'area_flotillas' => $this->flotillas ?? 0,
            'area_seminuevos' => $this->seminuevos ?? 0,
            'area_refacciones' => $this->refacciones ?? 0,
            'area_servicio' => $this->servicio ?? 0,
            'area_hyp' => $this->hyp ?? 0
        ];
    }
}
