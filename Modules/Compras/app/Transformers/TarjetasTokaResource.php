<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TarjetasTokaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $estatus = $this->setEstatusLabel($this->estatus);
        return [
            'id' => $this->id,
            'tarjeta' => $this->tarjeta,
            'proxy_number' => $this->proxy_number,
            'cuenta' => $this->cuenta,
            'nomina' => $this->nomina,
            'empresa_id' => $this->cliente?->id ?? '',
            'cliente'=> $this->cliente?->nombre_empresa ?? '',
            'estatus'=>  $estatus,
        ];
    }

    public function setEstatusLabel($estatus){
        return match ($estatus) {
            0 => 'No asignada',
            1 => 'Asignada',
            2 => 'Cancelada',
            default => 'Desconocido' 
        };
    }
}
