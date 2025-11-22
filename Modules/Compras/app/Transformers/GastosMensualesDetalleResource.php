<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GastosMensualesDetalleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $multiUnidad = $this->isMultiple($this->tipo, $this->usuario_destino);

         return [
            'idSolicitudCompra' => $this->idSolicitudCompra,
            'estatus' => $this->estatus,
            'intercompania' => $this->intercompania,
            'folio_solicitud' => $this->folio_solicitud,
            'fecha' => $this->fecha,
            'folio_orden_compra' => $this->folio_orden_compra,
            'proveedor' => $this->proveedor,
            'servicios' => $this->servicios,
            'nro_economico' => "ECO: $this->nro_economico" ?? "N/A",
            'total_por_folio' => (float) $this->total_por_folio,
            'multiUnidad' => $multiUnidad,
            'tipo' => $this->tipo,
            'usuario_destino' => $this->usuario_destino,

        ];
    }

    private function isMultiple($tipo ,$usuario_destino){
        if($tipo == 2 && $usuario_destino == 602) {
            return true;
        }
        return false;
    }
}
