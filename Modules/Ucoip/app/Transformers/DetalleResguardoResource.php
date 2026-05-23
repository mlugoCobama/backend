<?php

namespace Modules\Ucoip\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetalleResguardoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            // "ucoip_resguardo_ucoip_id" => 41,
            // "ucoip_hardware_id" => 282,
            "fecha_entrega" => $this->fecha_entrega,
            "fecha_devolucion" => $this->fecha_devolucion,
            "observaciones" => $this->observaciones,
            "caracteristicas" => $this->caracteristicas,
            "hardware" => new HardwareResource($this->hardware)
        ];
    }
}
