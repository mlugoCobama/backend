<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutotanqueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
        public function toArray(Request $request): array
    {
        $isAgencia = $this->setIsAgencia($this->intercompania);

        return[
            'intercompania' => $this->intercompania,
            "id" => $this->id,
            "firstname" => "$this->marca_vehiculo $this->submarca" ,
            "realname" => "$this->modelo $this->placas" ,
            "name" => '',
            "puesto" => "N/A",
            "Telfono" =>  "N/A",
            "direccion" => "N/A",
            // "intercompania" => $this->num_intercompania,
            "empresa" => $this->entidad ?? "N/A",
            "isAgencia"=> $isAgencia
        ];
    }

    /**
     * Valida si el usuario pertenece a una agencia en base al numero de intercompania
     */
    private function setIsAgencia($intercompania)
    {
        $interAgencias = array_flip([7102, 7075, 7074, 7072, 7071, 7064, 7063, 7062, 7061, 7051, 712, 710, 706]);
        return isset($interAgencias[$intercompania]); 
    }
}
