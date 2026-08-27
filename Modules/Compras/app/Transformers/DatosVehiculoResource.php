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
            'marca' => $this->marca,
            'submarca' =>  $this->submarca,
            'modelo' => $this->modelo,
            'no_serie' => $this->no_serie
        ];

    }
}
