<?php

namespace Modules\Nissan\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendedorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'tipo_texto' => $this->setTipoTexto($this->tipo),
            'procentaje_apv' => $this->porcentaje_apv,
            'procentaje_apv' => $this->porcentaje_apv,
            'nombre' => 'NO DISPONIBLE',
            'clave' => $this->nro_vendedor_as.'-'.$this->agencia,
            'nro_vendedor_as' => $this->nro_vendedor_as,
            'agencia' => $this->agencia,
            'agencia_nombre' => $this->setAgenciaNombre($this->agencia)
        ];
    }

    private function setTipoTexto($tipo){
        return $tipo == 1 ? 'Interno' : 'Externo';
    }

    private function setAgenciaNombre($idAgencia){
        $nombreAgencia = [710 => 'Nissan Universidad',
                            0 => 'Nissan Insurgentes',
                          730 => 'Nissan Azcapotzalco',
                          714 => 'Nissan Campestre',
                          1 => 'Renault Azcapotzalco',
                          2 => 'Renault Ecatepec',
                          3 => 'Renault Vallejo',
                          4 => 'Renault Pachuca',];
                          
        return $nombreAgencia[$idAgencia];

    }

    }

