<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProveedoresResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request)
    // : array
    {
        // return parent::toArray($request);
        return[

            'id'=>$this->id,
            'nombre'=>$this->nombre,
            'contacto'=>$this->contacto,
            'telefono'=>$this->telefono,
            'localidad'=>$this->localidad,
            'condiciones'=>$this->condiciones,
            'servicios'=>$this->servicios,
            'correo'=>$this->correo,
            'horario_atencion'=>$this->horario_atencion,
            'tiempo_entrega'=>$this->tiempo_entrega,
            'dias_credito'=>$this->dias_credito,
            'activo'=>$this->activo,
        ];
    }
}
