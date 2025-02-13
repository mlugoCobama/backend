<?php

namespace Modules\Dashboard\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnergeticosMensualResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->entidad,
            'fecha' => $this->fecha,
            'uno' => $this->uno,
            'gasto' => $this->gasto,
            'ventas' => $this->ventas,
            'venta_litros' => $this->venta_litros,
            'utilidad_bruta' => $this->utilidad_bruta,
            'personal' => $this->personal ?? 0,
            'ubo' => $this->ubo ?? 0,
            'eficiencia' => $this->eficiencia ?? 0,
        ];
    }
}
