<?php

namespace Modules\Capacitaciones\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class UsuarioPuestoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $usuario = $this->getNombreUsuario($this->id_usuario);

        return [
            'id' => $this->id,
            'id_usuario' => $this->id_usuario,
            'usuario' => $usuario['nombre_completo'],
            'id_puesto' => $this->id_puesto,
            'puesto' => $this->puesto->nombre,
            'empresa' => $usuario['empresa'],
            'intercompania' => $usuario['intercompania'],
            

        ];
    }
    
    /**
     *  Recupera los datos del usuario para asignarlos a la consulta
     */
    private function getNombreUsuario($usuarioId)
    {
        $usuario = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $usuarioId . ')');
        if (count($usuario) > 0) {
            return [
                'nombre_completo' => trim($usuario[0]->firstname . ' ' . $usuario[0]->realname),
                'empresa' => $usuario[0]->empresa ?? null,
                'intercompania' => $usuario[0]->intercompania ?? null,
            ];
        }
        return ['nombre_completo' => 'No asignado', 'empresa' => null, 'intercompania' => null,];
    }
}
