<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class AlmacenComprasResource extends JsonResource
{
     // Caché local para evitar consultas repetidas
    private static $usuariosCache = [];
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $usuario = $this->getNombreUsuario(1, $this->usuario_destino, $this->empresa);
        return [
            "solicitud_id" => $this->solicitud_id,
            "folio" => $this->folio,
            "empresa" => $usuario['empresa'],
            "usuario_destino" => $usuario['nombre_completo'],
            "fecha" => $this->fecha,
            "com_cat_sistemas_auto_id" => $this->com_cat_sistemas_auto_id,
            "categoria" => $this->categoria,
            "detalle_id" => $this->detalle_id,
            "cantidad" => $this->cantidad,
            "unidad" => $this->unidad,
            "descripcion" => $this->descripcion,
            "observaciones" => $this->observaciones,
            "estatus_almacen" => $this->estatus_almacen
        ];
    }

    /**
     *  Recupera los datos del usuario para asignarlos a la consulta
     */
    private function getNombreUsuario($tipoSolicitud, $usuarioId, $empresa)
    {
        $cacheKey = $tipoSolicitud . '_' . $usuarioId . '_' . $empresa;

        if (isset(self::$usuariosCache[$cacheKey])) {
            return self::$usuariosCache[$cacheKey];
        }

        if ($tipoSolicitud == 2) {
            $usuario = UsersResource::collection(DB::connection('dashboard')->select('call SistemaTickets.SP_GetDataAutotanque(' . $usuarioId . ',' . $empresa . ')'));

            $nombreCompleto = trim($usuario[0]->firstname . ' ' . $usuario[0]->realname . ' No. Serie:' . $usuario[0]->name);
        } else {
            $usuario = UsersResource::collection(DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $usuarioId . ')'));

            $nombreCompleto = trim($usuario[0]->firstname . ' ' . $usuario[0]->realname);
        }

        if (count($usuario) > 0) {
            $resultado = [
                'nombre_completo' => $nombreCompleto,
                'empresa' => $usuario[0]->empresa ?? null,
            ];
        } else {
            $resultado = [
                'nombre_completo' => 'No asignado',
                'empresa' => null,
            ];
        }

        self::$usuariosCache[$cacheKey] = $resultado;
        return $resultado;
    }
}
