<?php

namespace Modules\Nissan\Transformers;

use App\Enums\EstatusComisionesAutos;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeguroResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {

        return [
            "id" => $this->id,

            "folio" => $this->folio,
            "poliza" => $this->poliza,
            "fecha_emision" => $this->fecha_emision,

            "prima_neta" => $this->prima_neta,
            "comision_apv_pesos" => $this->comision_apv_pesos,

            "com_vendedores_id" => $this->com_vendedores_id,
            "agencia" => $this->agencia,

            "observaciones" => $this->observaciones,
            "comentario" => $this->comentario,
            "ruta_archivo" => $this->ruta_archivo ?? null,
            "estatus" => $this->estatus,
            "estatusTexto" => EstatusComisionesAutos::label($this->estatus),

            "activo" => $this->activo,

            "aseguradora" => $this->aseguradora ?? 'N/D',
            "nombre" => $this->nombre ?? 'N/D',
            "unidad" => $this->unidad ?? 'N/D',
            "serie" => $this->serie ?? 'N/D',
            "forma_pago" => $this->forma_pago ?? 'N/D',
            "com_encargado_seg"=> $this->com_encargado_seg ?? 0,
            "vs"=> $this->vs ?? 0,
            "no_vendedor" => $this->vendedor->nro_vendedor_as ?? '-', 
            // Relación vendedor
            "vendedor" => $this->whenLoaded('vendedor', function () {
                return  ($this->vendedor->nombre ?? 'No disponible');
            }, 'No disponible'),
        ];
    }
}
