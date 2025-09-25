<?php

namespace Modules\Capacitaciones\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmodulosAsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
       return[
            'id' => $this->id,
            'nombre' => $this->nombre,
            'permiso' => $this->permiso
        ];
    }
}
