<?php

namespace Modules\Nissan\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConcentradoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $comFactura = ($this->total ?? 0) * 0.12;
        $descNomina = $this->desc_nomina ?? 0;
        $prestaciones = $this->prestaciones ?? 0;
        $descCasa = $this->desc_c_casa ?? 0;
        $otrosDescuentos = $this->otros_descuentos ?? 0;
        $infonavit = $this->infonavit ?? 0;
        $nomina = $this->nomina ?? 0;
        $mDispersar = $this->total - $comFactura - ($descNomina + $prestaciones + $descCasa + $otrosDescuentos+ $infonavit) + $nomina;


        return [
            'id' => $this->id, 
            'nro_vendedor_as' => $this->nro_vendedor_as, 
            'vendedor' => $this->vendedor, 
            'nuevos' => $this->nuevos, 
            'pend_nuevos' => ($this->nuevos == 0 && $this->pend_nuevos > 0), 
            'seminuevos' => $this->seminuevos, 
            'pend_seminuevos' => ($this->seminuevos == 0 && $this->pend_seminuevos > 0), 
            'financiamiento' => $this->financiamiento, 
            'pend_financiamiento' => ($this->financiamiento == 0 && $this->pend_financiamiento > 0), 
            'accesorios' => $this->accesorios, 
            'pend_accesorios' => ($this->accesorios == 0 && $this->pend_accesorios > 0), 
            'seguros' => $this->seguros, 
            'pend_seguros' => ($this->seguros == 0 && $this->pend_seguros > 0), 
            'toma_unidades' => $this->toma_unidades,
            'pend_toma' => ($this->toma_unidades == 0 && $this->pend_toma > 0), 
             
            'otros' => $this->otros ?? 0, 
            'total' => $this->total,
            
            'desc_nomina' => $descNomina,
            'prestaciones' => $prestaciones,
            'comision_factura' => $comFactura,
            'otros_descuentos' => $otrosDescuentos,
            'desc_c_casa' => $descCasa,
            'infonavit' => $infonavit,
            'nomina' => $nomina, 
            'monto_dispersar' => $mDispersar
        ];
    }
}
