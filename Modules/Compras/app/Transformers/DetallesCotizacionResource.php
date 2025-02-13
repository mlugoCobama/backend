<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetallesCotizacionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request)
    {
        return[
            'id'=>$this->id,
            'importe_unitario'=>$this->importe_unitario,
            'detalle_solicitud'=>new DetalleSolicitudCompraResource($this->detalle_solicitud),
            'cotizaciones_proveedores_proveedores_id'=>$this->cotizaciones_proveedores_proveedores_id,
        ];
    }
}
