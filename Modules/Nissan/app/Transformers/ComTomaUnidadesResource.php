<?php

namespace Modules\Nissan\Transformers;

use App\Enums\EstatusComisionesAutos;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComTomaUnidadesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "agencia" => $this->agencia,
            "por_inventario" => $this->no_inventario,
            "vehiculo" =>$this->vehiculo ?? 'No disponible',
            "numero_serie" =>$this->no_serie ?? 0000000,
            "tipo_apv" =>$this->tipo_apv,
            "fecha_toma" =>$this->fecha_toma,
            "comision_apv_pesos" =>$this->comision_apv_pesos,
            "com_vendedores_id" => $this->com_vendedores_id,
            "observaciones" => $this->observaciones,
            "estatus" => $this->estatus,
            "comentario" => $this->comentario,
            "estatusTexto" => EstatusComisionesAutos::label($this->estatus),
            "activo" => $this->activo,
            "no_vendedor" => $this->vendedor->nro_vendedor_as ?? 'No disponible',
            "vendedor" => ($this->vendedor->nombre ?? 'No disponible') ?? 'Desconocido',
        ];
    }
}
