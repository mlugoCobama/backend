<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Compras\Models\DetalleSolicitud;

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
            'detalle_solicitud'=>new DetalleSolicitudCompraResource($this->detalle_solicitud->confirmadas()),
            // 'detalle_solicitud' => new DetalleSolicitudCompraResource(
            //     DetalleSolicitud::confirmadas()->find($this->detalle_solicitud_id)
            // ),


            'cotizaciones_proveedores_proveedores_id'=>$this->cotizaciones_proveedores_proveedores_id,
        ];
    }
}
