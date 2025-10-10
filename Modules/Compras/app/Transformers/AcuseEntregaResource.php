<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcuseEntregaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ruta' => $this->ruta,
            'comentario' => $this->comentario,
            'fecha' => $this->fecha,
            'orden_compra_id' => $this->orden_compra_id,
        ];

    }
}
