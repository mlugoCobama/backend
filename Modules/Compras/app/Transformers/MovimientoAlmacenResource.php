<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class MovimientoAlmacenResource extends JsonResource
{
     // Caché local para evitar consultas repetidas
    private static $usuariosCache = [];
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $usuario_entrega = $this->getNombreUsuario(1, $this->usuario_entrega);
        $usuario_recibe = $this->getNombreUsuario(1, $this->usuario_recibe);
        $movimiento = $this->getMovimientoDescription($this->tipo_movimiento);
        return [
            "fecha" => $this->fecha_movimiento,
            "id_almacen" => $this->id_almacen,
            // "folio" => $this->folio,
            "tipo_movimiento" => $this->tipo_movimiento,
            'texto_movimiento' => $movimiento,
            "cantidad" => $this->cantidad,
            "descripcion" => $this->descripcion,
            "id_almacen" => $this->id_almacen,
            "usuario_entrega" => $usuario_entrega['nombre_completo'],
            "usuario_recibe" => $usuario_recibe['nombre_completo'],
        
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

        $usuario = UsersResource::collection(DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $usuarioId . ')'));
        
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

    private function getMovimientoDescription($tipoMovimiento){
        return match ($tipoMovimiento) {
            's' =>  'Salida',
            'e' =>  'Entrada',
            'd' =>  'Devolución',
            default => 'No definido'
        };
    }
}