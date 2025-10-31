<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpedientesProveedoresResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request)
    {
        // return parent::toArray($request);

        return[
            // 'id'=> $this->id,
        'constancia_fiscal' => $this->constancia_fiscal ?? null,
        'ine'  => $this->ine ?? null,
        'comprobante_domicilio'  => $this->comprobante_domicilio ?? null,
        'estado_cuenta'  => $this->estado_cuenta ?? null,
        'acta_constitutiva'  => $this->acta_constitutiva ?? null,
        'proveedores_id'  => $this->proveedores_id ?? null,
        'poder_notarial' => $this->poder_notarial ?? null,
        'contrato' => $this->contrato ?? null,
        'opinion_cumplimiento' => $this->opinion_cumplimiento ?? null,
        ];

    }
}
