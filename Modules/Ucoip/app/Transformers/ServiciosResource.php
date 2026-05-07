<?php

namespace Modules\Ucoip\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiciosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request)
    {
        return [ 
            'id' => $this->id,
            'intercompania' => $this->intercompania, 
            'empresa' => $this->intercompania, 
            'proveedor_id' => $this->proveedor_id,
            'proveedor' => $this->proveedor->nombre,
            'tipo_servicio_id' => $this->tipo_servicio_id,
            'tipo_servicio' => $this->tipo_servicio->nombre,
            'nombre' => $this->nombre,
            'identificador_externo' =>  $this->identificador_externo,
            'costo_base' =>  $this->costo_base,
            'periodicidad' => $this->periodicidad,
            'fecha_inicio' => $this->periodicidad,
        ];
    } 

}
