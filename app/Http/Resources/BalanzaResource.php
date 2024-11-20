<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BalanzaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'cta' => $this->cta,
            'scta' => $this->scta,
            'sscta' => $this->sscta,
            'ssscta' => $this->ssscta,
            'descripcion' => $this->descripcion,
            'saldo_inicial' => $this->saldo_inicial,
            'debe' => $this->debe,
            'haber' => $this->haber,
            'saldo_actual' => $this->saldo_actual,
        ];
    }
}
