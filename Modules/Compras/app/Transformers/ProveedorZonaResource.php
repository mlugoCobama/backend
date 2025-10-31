<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProveedorZonaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'zona' => $this->nombre_zona,
            'nombre' => $this->contacto->nombre,
            'correo' => $this->contacto->correo,
            'telefono' => $this->contacto->telefono,
            'notas' => $this->contacto->notas,
        ];
    }
}
