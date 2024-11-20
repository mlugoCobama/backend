<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresasResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'division' => $this->division,
            'sub_division' => $this->sub_division,
            'nombre' => $this->entidad,
            'sucursal' => $this->sucursal,
            'id' => $this->sucursal_id,
        ];
    }
}
