<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DatosPagoProveedorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return
        [
            'id' => $this->id,
            'banco' => $this->banco,
            'no_cuenta' => $this->no_cuenta,
            'clave_interbancaria' => $this->clave_interbancaria,
            'beneficiario' => $this->beneficiario,
            'proveedor_id' => $this->proveedor_id
        ];
    }
}
