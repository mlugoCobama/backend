<?php

namespace Modules\Capacitaciones\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModulosSubmodulosResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'permiso' => $this->permiso,
            'submodulo' => new SubmodulosAsResource($this->Submodulo),
            'funciones' => FuncionesAsResource::collection($this->funciones),
            'activo' =>  false
        ];
    }
}
