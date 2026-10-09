<?php

namespace Modules\ParqueVehicular\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvidenciaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folio' => $this->folio ?? 'SF-000000',
            'ruta' => $this->ruta,
            'imagen' => url($this->ruta),
            'nombre' => $this->nombre,
            'pv_orden_proveedor_id' => $this->pv_orden_proveedor_id,
            'media_type' => $this->media_type,
            'categoria' => $this->categoria,
            'descripcion' => $this->descripcion
        ];
    }
}
