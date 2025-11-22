<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GastosMensualesConcentradoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'num_intercompania' => $this->num_intercompania,
            'empresa' => $this->empresa,
            'solicitudes' => (int) $this->solicitudes,
            'total_por_empresa' => (float) $this->total_por_empresa,
        ];
    }
}
