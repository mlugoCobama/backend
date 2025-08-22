<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DatosVehiculoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'eco' => "ECO: $this->nro_economico",
            // 'nombre' => $this->nombre,
            // 'capacidad' => $this->capacidad,
            // 'tipo_combustible' => $this->tipo_combustible,
            // Agrega los campos que necesites
        ];

    }
}
