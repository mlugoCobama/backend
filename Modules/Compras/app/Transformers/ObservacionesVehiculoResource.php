<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ObservacionesVehiculoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'fecha' => $this->created_at->format('d/m/Y H:i'),
            'observacion' => $this->observaciones,
            'usuario'=> $this->getNombreUsuario($this->user_id) ,
        ];
    }

    private function getNombreUsuario($usuarioId)
    {
        if (!empty($usuarioId)) {
            $usuario = UsersResource::collection(DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' .  $usuarioId . ')'));
            $nombreCompleto = trim($usuario[0]->firstname . ' ' . $usuario[0]->realname);

            if (count($usuario) > 0) {
                return  $nombreCompleto;
            }
        }

        return 'No especificado';
    }
}
