<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class DispersionesTokaResource extends JsonResource

{
    private static $usuariosCache = [];
    
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $usuario = $this->getNombreUsuario(1, $this->usuario_solicita);
        return [
            "id" => $this->id,
            "inicio_periodo" => $this->inicio_periodo,
            "fin_periodo" => $this->fin_periodo,
            "precio_combustible" => $this->precio_combustible,
            "folio" => $this->folio,
            "usuario_solicita" => 2395,
            "solicita" => $usuario['nombre_completo'],
            "empresa" => $usuario['empresa'],
            "area" => $usuario['area'],
            "fecha" => $this->fecha,
            "estatus" => 1,
            "activo" => 1,
            "fecha_dispersion" => $this->fecha_dispersion 
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

        if ($tipoSolicitud == 2) {
            $nombreCompleto = 'N/D';
        } else {
            $usuario = UsersResource::collection(DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $usuarioId . ')'));
            $nombreCompleto = trim($usuario[0]->firstname . ' ' . $usuario[0]->realname);
        }

        if (count($usuario) > 0) {
            $resultado = [
                'nombre_completo' => $nombreCompleto,
                'empresa' => $usuario[0]->empresa ?? '',
                'area' => $usuario[0]->area ?? '',
            ];
        } else {
            $resultado = [
                'nombre_completo' => 'No asignado',
                'empresa' => '',
                'area' => $usuario[0]->area ?? '',
            ];
        }

        self::$usuariosCache[$cacheKey] = $resultado;
        return $resultado;
    }
}
