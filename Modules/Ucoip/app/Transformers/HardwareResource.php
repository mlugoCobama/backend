<?php

namespace Modules\Ucoip\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Ucoip\Models\CatHardwareModel;

class HardwareResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'no_serie' => $this->no_serie,
            'tipo_cpu' => $this->tipo,
            'mac' => $this->mac,
            'disco_duro' => $this->disco_duro,
            'memoria_ram' => $this->memoria_ram,
            'procesador' => $this->procesador,
            'caracteristicas' => $this->caracteristicas,
            'observaciones' => $this->observaciones,
            'estatus' => $this->estatus,
            'estado' => $this->estado,
            'tipo' => new CatHardwareResource($this->Tipo),
            'id_empresa' => $this->empresa->id ?? null,
            'empresa' => $this->empresa->nombre ?? 'No Asignado'
        ];
    }
}
