<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetalleAutotanqueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'com_detalle_solicitud_id' => $this->com_detalle_solicitud_id,
            'com_datos_vehiculos_id' => $this->com_datos_vehiculos_id,
            'DatosVehiculo'=> new DatosVehiculoResource($this->DatosVehiculo),
        ];


    }
}
