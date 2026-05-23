<?php

namespace Modules\Ucoip\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResguardoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_usuario_asignado' => $this->id_usuario_asignado,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'comentarios' => $this->comentarios,
            'admin_rt' => $this->admin_rt,
            // 'detalles' => $this->detalles
            'detalles' => DetalleResguardoResource::collection($this->detalles)
        ];
    }
}
