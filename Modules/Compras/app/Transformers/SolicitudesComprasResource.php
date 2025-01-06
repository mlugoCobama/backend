<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudesComprasResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return[
            'id'=>$this->id,
            'folio'=>$this->folio,
            'usuario_solicita'=>$this->usuario_solicita,
            'usuario_destino'=>$this->usuario_destino,
            'motivo'=>$this->motivo,
            'fecha'=>$this->fecha,
            'estatus'=>$this->estatus,
            'activo'=>$this->activo,
            //'detalle' => DetalleSolicitudCompraResource::collection($this->DetallesSolicitud)
        ];
    }
}
