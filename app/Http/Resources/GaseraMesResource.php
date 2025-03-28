<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GaseraMesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'entidad' => $this->entidad,
            'estacion' => $this->estacion,
            'fecha' => date_format( date_create( $this->fecha ), 'd-m-Y' ),
            'uno' => $this->uno,
            'ubo' => $this->ubo,
            'gasto' => $this->gasto,
            'ventas' => $this->ventas,
            'venta_litros' => $this->venta_litros,
            'utilidad_bruta' => $this->utilidad_bruta,
            'personal' => $this->personal,
            'eficiencia' => $this->eficiencia,
        ];
    }
}
