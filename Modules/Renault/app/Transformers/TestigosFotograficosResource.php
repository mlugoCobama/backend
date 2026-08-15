<?php

namespace Modules\Renault\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestigosFotograficosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'folio' => $this->folio,
            'ruta' => $this->ruta,
            'imagen' => url('api/storage/'.$this->ruta.$this->nombre),
            'nombre' => $this->nombre,
            'ren_entrada_vehiculo_id' => $this->ren_entrada_vehiculo_id,
            'media_type' => $this->media_type,
            'categoria' => $this->categoria,
            'descripcion' => $this->descripcion
        ];
    }
}
