<?php

namespace Modules\Nissan\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatGastosPredefinidosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {

        $nombreAgencia = $this->setAgenciaNombre($this->agencia);
        return [
            'id' => $this->id,
            'clave_gasto' => $this->clave_gasto,
            'importe' => $this->importe,
            'agencia_id' => $this->agencia,
            'agencia' => $nombreAgencia,
            'tipo_vehiculo' => $this->tipo_vehiculo,
            'activo' => $this->activo
        ];
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

        return $nombreAgencia[$idAgencia] ?? 'Agencia no disponible';

    }
}
