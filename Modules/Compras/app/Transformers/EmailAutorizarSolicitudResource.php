<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class EmailAutorizarSolicitudResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
        {
        $usuarioDestino =  $this->getNombreUsuario($this->usuario_destino);
        $usuarioSolicita =  $this->getNombreUsuario($this->usuario_solicita);
        $datosCC = $this->asignarDescripcionCC($this->c_c);

        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'motivo' => $this->motivo,
            'fecha' => $this->fecha,
            'c_c' => $this->c_c,
            'centro_costo' => $datosCC['descripcion'],
            'usuario_destino' => $usuarioDestino['nombre_completo'],
            'empresa' => $usuarioDestino['empresa'],
            'intercompania' => $usuarioDestino['intercompania'],
            'usuario_solicita' => $usuarioSolicita['nombre_completo'],
            'auto_admin' => $this->auto_admin,
            'auto_gg' => $this->auto_gg,
            'DetallesSolicitud' => DetalleSolicitudCompraResource::collection($this->DetallesSolicitud)
        ];
    }

    /**
     * Recupera el nombre de usuario en base al id
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
        return ['nombre_completo' => 'No asignado', 'empresa' => null];
    }

    /**
     * Asigna una descripción del centro de costos
     */
    private function asignarDescripcionCC($cc){
        $rawCentrosCosto = File::get(base_path('\Modules\Compras\resources\assets\json\centrosCostos\catCentrosCostos.json'));
        $jsonCC = json_decode(json: $rawCentrosCosto, associative: true);
        $dataCC = $jsonCC[$cc];
        return $dataCC;
    }

    
}
