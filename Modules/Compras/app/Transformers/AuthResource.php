<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            
            "id" => $this->id,
            "name" => "$this->firstname $this->realname",
            "email" => $this->name,
            "password" => $this->password,
            "email_verified_at" => null,
            "activo" => $this->is_active,
            "tipo" => 1,
            "intercompania" => $this->intercompania,
        ];
    }
}
