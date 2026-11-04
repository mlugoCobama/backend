<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetalleEntregaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id_detalle' => $this->id,
            'cant' =>  $this->cantidad,
            'unidadMedida' => $this->entrada->detalle->unidadMedida->nombre ?? 'N/A',
            'descripcion' =>  $this->entrada->detalle->descripcion ?? 'Descripcion no disponible',
            'observaciones' =>  $this->entrada->detalle->observaciones ?? 'Descripcion no disponible',
            'tipo' => $this->tipo,


        ];
    }
}
