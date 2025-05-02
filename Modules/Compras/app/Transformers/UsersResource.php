<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class usersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $isAgencia = $this->setIsAgencia($this->intercompania);

        return[
            "id" => $this->id,
            "firstname" => $this->firstname,
            "realname" => $this->realname,
            "name" => $this->name,
            "puesto" => $this->puesto,
            "Telfono" => $this->Telefono,
            "direccion" => $this->direccion,
            "intercompania" => $this->intercompania,
            "empresa" => $this->empresa,
            "isAgencia"=> $isAgencia

        ];
    }

    private function setIsAgencia($intercompania)
    {
        $interAgencias = array_flip([7102, 7075, 7074, 7072, 7071, 7064, 7063, 7062, 7061, 7051, 712, 710, 706]);
        return isset($interAgencias[$intercompania]); 
    }
}