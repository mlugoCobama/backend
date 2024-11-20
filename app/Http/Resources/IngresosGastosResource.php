<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngresosGastosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'codigo' => $this->codigo,
            'descripcion' => $this->descripcion,
            'contable' => $this->contable,
            'ajuste_ingresos' => $this->ajuste_ingresos,
            'conciliable' => $this->conciliable,
            'planeacion_favor' => $this->planeacion_favor,
            'ajuste_auditoria' => $this->ajuste_auditoria,
            'rt' => $this->rt,
            'neto' => $this->neto,
        ];
    }
}
