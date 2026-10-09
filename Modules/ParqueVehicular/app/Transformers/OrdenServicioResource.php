<?php

namespace Modules\ParqueVehicular\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdenServicioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'com_datos_vehiculos_id' => $this->com_datos_vehiculos_id,
            'com_proveedores_id' => $this->com_proveedores_id,
            'descripcion_falla' => $this->descripcion_falla,
            'fecha_inicio' => $this->fecha_inicio,
            'observaciones' => $this->observaciones,
            'cotizacion' => $this->cotizacion ? url($this->cotizacion) : null,
            'ruta_cotizacion' => $this->cotizacion ? $this->cotizacion : null,
            'proveedor_asignado' => new ProveedorAsignadoResource($this->whenLoaded('proveedorAsignado')),
            'vehiculo' => $this->whenLoaded('vehiculo'),
            ];
    }
}
