<?php

namespace Modules\Macro\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TecnicoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['tecnico']->id,
            'nombre' => $this['tecnico']->nombre,
            'apellidos' => $this['tecnico']->apellidos,
            'tipo' => $this['tecnico']->tipo,
            'intercompania' => $this['tecnico']->intercompania ?? 333,
            'activo' => $this['tecnico']->activo,
            'empresa' => $this['empresa'] ?? 'No especificada'
        ];
    }
}
