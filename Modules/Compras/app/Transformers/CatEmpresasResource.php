<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatEmpresasResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $isAgencia = $this->setIsAgencia($this->intercompania);
        return [
            "name" => $this->name,
            "intercompania" => (int)$this->intercompania,
            "isAgencia" => $isAgencia
        ];
    }

    /**
     * Valida si a empresa es una agencia por medio del numero de "intercompania"
     */
    private function setIsAgencia($intercompania)
    {
        $interAgencias = array_flip([7102, 7075, 7074, 7072, 7071, 7064, 7063, 7062, 7061, 7051, 712, 710, 706]);
        return isset($interAgencias[$intercompania]); 
    }
}
