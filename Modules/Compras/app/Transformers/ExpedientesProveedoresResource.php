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
        'constancia_fiscal' => $this->constancia_fiscal,
        'ine'  => $this->constancia_fiscal,
        'comprobante_domicilio'  => $this->constancia_fiscal,
        'estado_cuenta'  => $this->constancia_fiscal,
        'acta_constitutiva'  => $this->constancia_fiscal,
        'proveedores_id'  => $this->constancia_fiscal,
        'poder_notarial' => $this->constancia_fiscal
        ];

    }
}
