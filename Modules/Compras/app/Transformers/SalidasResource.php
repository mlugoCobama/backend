<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class SalidasResource extends JsonResource
{

    // Caché local para evitar consultas repetidas
    private static $usuariosCache = [];


    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {

        $usuario_recibe = $this->getNombreUsuario(1, $this->tecnico_id);
        $usuario_entrega = $this->getNombreUsuario(1, $this->usuario_entrega);

        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'fecha' => $this->fecha,
            'usuario_entrega' => $usuario_entrega['nombre_completo'],
            'usuario_recibe' => $usuario_recibe['nombre_completo'],
            'detalles' => DetalleEntregaResource::collection($this->movimientosEntrega)
        ];
    }

    /**
     *  Recupera los datos del usuario para asignarlos a la consulta
     */
    private function getNombreUsuario($tipoSolicitud, $usuarioId)
    {
        $cacheKey = $tipoSolicitud . '_' . $usuarioId;

        if (isset(self::$usuariosCache[$cacheKey])) {
            return self::$usuariosCache[$cacheKey];
        }

        $usuario = UsersResource::collection(DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(?)', [$usuarioId]));

        if (count($usuario) > 0) {
            $nombreCompleto = trim($usuario[0]->firstname . ' ' . $usuario[0]->realname);
            $resultado = [
                'nombre_completo' => $nombreCompleto,
                // 'empresa' => $usuario[0]->empresa ?? null,
            ];
        } else {
            $resultado = [
                'nombre_completo' => 'No asignado',
                // 'empresa' => null,
            ];
        }

        self::$usuariosCache[$cacheKey] = $resultado;
        return $resultado;
    }
}
