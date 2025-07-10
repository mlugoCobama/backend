<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Compras\Models\OrdenTrabajo;

class OrdenTrabajoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "orden_trabajo" => $this->orden_trabajo,
            "datos_vehiculo" => $this->com_datos_vehiculo_id,
            "solicitu_compra" => $this->com_solicitudes_compra_id,
        ];
        
    }
}
