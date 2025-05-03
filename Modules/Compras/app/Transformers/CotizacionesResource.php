<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CotizacionesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return[
            'id'=>$this->id,
            'folio'=>$this->folio,
            'fecha'=>$this->fecha,
            'consideraciones'=>$this->consideraciones,
            'solicitudes_compra_id'=>$this->solicitudes_compra_id,
            // 'SolicitudCompra'=> new SolicitudesComprasResource($this->SolicitudCompra)
        ];
    }
}
