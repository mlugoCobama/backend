<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatUnidadesMedidaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request)
    {
        return[
            'id'=>$this->id,
            'nombre'=>$this->nombre,
            'abreviatura'=>$this->abreviatura
        ];
    }
}
