<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CotizacionesProveedoresResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return[
            'id'=>$this->id,
            'proveedores_id'=> new ProveedoresResource($this->DatosProveedor),
            'cotizaciones_id'=>$this->cotizaciones_id,
            'ruta'=>$this->ruta,
        ];
    }
}
