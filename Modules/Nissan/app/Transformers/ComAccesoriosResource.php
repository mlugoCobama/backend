<?php

namespace Modules\Nissan\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComAccesoriosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $estatusTexto = $this->setEstatusTexto($this->estatus);

        return [
            "id" => $this->id,

            "no_factura" => $this->no_factura,
            "comision_apv_pesos" => $this->comision_apv_pesos,
            "sub_total_factura" => $this->sub_total_factura,
            "com_vendedores_id" => $this->com_vendedores_id,
            "observaciones" => $this->observaciones,
            "comentario" => $this->comentario,
            "estatus" => $this->estatus,
            "estatusTexto" => $estatusTexto,
            "activo" => $this->activo,
            // Relación vendedor
            "vendedor" => $this->whenLoaded('vendedor', function () {
                return $this->vendedor->nro_vendedor_as . ' ' . ($this->vendedor->nombre ?? 'No disponible');
            }, 'No disponible'),

            "detalles" => $this->detalles
        ];
    }

    public function setEstatusTexto($estatus)
    {
        return match ($estatus) {
            1 => 'Por Autorizar',
            2 => 'Autorizada',
            3 => 'Pagada',
            4 => 'Rechazada',
            default => 'Desconocido'
        };
    }
}
