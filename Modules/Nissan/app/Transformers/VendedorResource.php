<?php

namespace Modules\Nissan\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendedorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'procentaje_apv' => $this->porcentaje_apv,
            'procentaje_apv' => $this->porcentaje_apv,
            'nombre' => 'ND',
            'clave' => $this->nro_vendedor_as.'-'.$this->agencia,
            'nro_vendedor_as' => $this->nro_vendedor_as,
            'agencia' => $this->agencia
        ];
    }
}
