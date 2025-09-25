<?php

namespace Modules\Capacitaciones\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FuncionesAsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'nombre' => $this->nombre,
            'ruta_video' => $this->ruta_video,
            'permiso' => $this->permiso
        ];
    }
}
