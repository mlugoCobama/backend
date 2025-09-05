<?php

namespace Modules\Macro\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlmacenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "empresa" => $this->empresa,
            "fecha_entrada" => Carbon::parse($this->fecha_entrada)->format('d/m/Y h:i A'),
            "cantidad" => $this->cantidad,
            "cant_recibida" => $this->cant_recibida,
            "existencia" => $this->existencia,
            "nombre" => $this->nombre,
            "abreviatura" => $this->abreviatura,
            "descripcion" => $this->descripcion,
            "solicitudes_compra_id" => $this->solicitudes_compra_id,
            "estatus_almacen" => $this->estatus_almacen,
            "eco" => $this->eco,
            "unidad" => $this->unidad,
            "eco_detalle" => $this->eco_detalle,
            "unidad_detalle" => $this->unidad_detalle,
            "observaciones" => $this->observaciones
        ];
    }
}
