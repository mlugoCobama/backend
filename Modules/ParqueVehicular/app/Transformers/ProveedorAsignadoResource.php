<?php

namespace Modules\ParqueVehicular\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProveedorAsignadoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'com_proveedores_id' => $this->com_proveedores_id,
            'diagnostico' => $this->diagnostico ?? null,
            'proveedor' => $this->whenLoaded('proveedor') ?? null,
            'pv_orden_servicio_id' => $this->pv_orden_servicio_id,
            'trabajos' => $this->whenLoaded('trabajos') ?? null,
            'evidencias' => EvidenciaResource::collection($this->whenLoaded('evidencias')) ??  null,
            'eventos' => $this->whenLoaded('eventos') ??  null,
            'autorizado' => $this->autorizado,
            'cotizacion' => $this->cotizacion ? url('storage/' . $this->cotizacion) :  null,
            'ruta_cotizacion' =>$this->cotizacion ?  $this->cotizacion : null,
        ];
    }
}
