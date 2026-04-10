<?php

namespace Modules\Nissan\Transformers;

use App\Enums\EstatusComisionesAutos;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComAccesoriosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "agencia" => $this->agencia,
            "razon_social" => $this->razon_social,
            "no_factura" => $this->no_factura,
            "no_pedido" => $this->no_pedido,
            "fecha_factura" => $this->fecha_factura,
            "comision_apv_pesos" => $this->comision_apv_pesos,
            "sub_total_factura" => $this->sub_total_factura ?? 0,
            "iva" => $this->iva ?? 0,
            "total" => ($this->sub_total_factura ?? 0) + ($this->iva ?? 0),
            "com_vendedores_id" => $this->com_vendedores_id,
            "observaciones" => $this->observaciones,
            "comentario" => $this->comentario,
            "estatus" => $this->estatus,
            "estatusTexto" => EstatusComisionesAutos::label($this->estatus),
            "factura_vehiculo" => $this->factura_vehiculo,
            'unidad' =>  $this->venta ?  $this->venta->descripcion : 'No Disponible',
            'serie' =>  $this->venta ?  $this->venta->serie : 'No Disponible',

            "activo" => $this->activo,
            // Relación vendedor
            "vendedor" => $this->whenLoaded('vendedor', function () {
                return $this->vendedor->nro_vendedor_as . ' ' . ($this->vendedor->nombre ?? 'No disponible');
            }, 'No disponible'),

            "detalles" => $this->detalles
        ];
    }
}
