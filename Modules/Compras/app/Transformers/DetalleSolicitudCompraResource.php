<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetalleSolicitudCompraResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request)
    {
        return[
            'id'=>$this->id,
            'cantidad'=>$this->cantidad,
            'descripcion'=>$this->descripcion,
            'observaciones'=>$this->observaciones,
            'unidadMedida'=> new CatUnidadesMedidaResource($this->CatUnidades),
            // 'img_referencia'=>$this->img_referencia
            'img_referencia' => $this->img_referencia ? url('storage/' . $this->img_referencia) : null,
            'solicitudes_compra_id' => $this->solicitudes_compra_id,
        ];
    }
}
