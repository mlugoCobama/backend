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
            'id'=> $this->id,
            'constancia_fiscal'=> $this->constancia_fiscal,
            'ine'=> $this->ine,
            'comprobante_domicilio'=> $this->comprobante_domicilio,
            'estado_cuenta'=> $this->estado_cuenta,
            'acta_constitutiva'=> $this->acta_constitutiva,
            'poder_notarial'=> $this->poder_notarial,
            
            'proveedores_id'=> $this->proveedores_id,
        ];

    }
}
