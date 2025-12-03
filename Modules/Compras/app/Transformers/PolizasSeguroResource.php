<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolizasSeguroResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "aseguradora" =>  $this->aseguradora,
            "inciso_vehiculo" => $this->inciso_flotilla,
            "cobertura" =>  $this->cobertura,
            "ramo" => $this->ramo,
            "sub_ramo" => $this->sub_ramo,
            "tipo_movimiento" => $this->tipo_movimiento,
            "prima_total" => $this->prima_total,
            "inicio_vigencia" => $this->formatFecha($this->inicio_vigencia),
            "fin_vigencia" =>  $this->formatFecha($this->fin_vigencia),
            "flotilla" =>  $this->flotilla,
            "inciso_foltilla" =>  $this->inciso_foltilla,
            "id_com_datos_vehiculo" =>  $this->inciso_vehiculo,
            "fecha_renovacion" =>   $this->formatFecha($this->fecha_renovacion ?? null),
        ];
    }

    public function formatFecha($fecha_original){
        if(isset($fecha_original) && !empty($fecha_original)){
            return date("d/m/Y", strtotime(str_replace("/", "-", $fecha_original)));
        }
        return null;
    }
}
