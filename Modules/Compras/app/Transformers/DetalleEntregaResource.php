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
            'id_detalle' => $this->id ?? null,
            'cant' =>  $this->cantidad,
            'unidadMedida' => $this->entrada->detalle->unidadMedida->nombre ?? $this->almacenProducto->producto->unidadMedida->nombre ?? 'N/A',
            'descripcion' =>  $this->entrada->detalle->descripcion ?? $this->almacenProducto->producto->descripcion_planta ?? 'Descripcion no disponible',
            'observaciones' =>  $this->entrada->detalle->observaciones ?? $this->almacenProducto->producto->descripcion_proveedor ?? 'Sin Observaciones',
            'tipo' => $this->tipo,


        ];
    }
}
