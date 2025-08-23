<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\Compras\Models\OrdenTrabajo;

class SolicitudesComprasResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array


    {
        $usuarioDestino =  $this->getNombreUsuario( $this->tipo ,$this->usuario_destino, $this->empresa);
        $usuarioSolicita =  $this->getNombreUsuario( 1 ,$this->usuario_solicita, $this->empresa);
        $estadoInfo = $this->asignarEstado($this->estatus);
        $datosCC = $this->asignarDescripcionCC($this->c_c);
        $ordenTrabajo = $this->getOrdenTrabajo($this->id);

        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'motivo' => $this->motivo,
            'fecha' => $this->fecha,
            'c_c' => $this->c_c,
            'centro_costo' => $datosCC['descripcion'],
            'id' => $this->usuario_destino,
            'usuario_destino' => $usuarioDestino['nombre_completo'],
            'empresa' => $usuarioDestino['empresa'],
            'usuario_solicita' => $usuarioSolicita['nombre_completo'],
            'estatus' => $this->estatus,
            'estado' => $estadoInfo['estado'],
            'claseEstado' => $estadoInfo['claseEstado'],
            'auto_admin' => $this->auto_admin,
            'auto_gg' => $this->auto_gg,
            'auto_macro' => $this->auto_macro,
            'tipo' => $this->tipo,
            'orden_trabajo' => $ordenTrabajo
        ];
    }

    /**
     *  Recupera los datos del usuario para asignarlos a la consulta
     */
    private function getNombreUsuario($tipoSolicitud , $usuarioId, $empresa)
    {
        if($tipoSolicitud == 2){
            $usuario = UsersResource::collection(DB::connection('dashboard')->select('call SistemaTickets.SP_GetDataAutotanque('. $usuarioId.','.$empresa.')'));
        }else{
            $usuario = UsersResource::collection(DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' .  $usuarioId . ')'));
        }
        // $usuario = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $usuarioId . ')');

        if (count($usuario) > 0) {
            return [
                'nombre_completo' => trim($usuario[0]->firstname . ' ' . $usuario[0]->realname),
                'empresa' => $usuario[0]->empresa ?? null,
            ];
        }
        return ['nombre_completo' => 'No asignado', 'empresa' => null];
    }

    /**
     * Asigna los datos del status como una etiqueta y una clase css
     */
    private function asignarEstado($estatus)
    {
        $estados = [
            1 => ['estado' => 'ESP. AUT. PLANTA', 'claseEstado' => 'badge-soft-info'],
            2 => ['estado' => 'SOLICITADO', 'claseEstado' => 'bg-info'],
            3 => ['estado' => 'EN COTIZACIÓN', 'claseEstado' => 'badge-soft-warning'],
            5 => ['estado' => 'ORDEN DE COMPRA', 'claseEstado' => 'bg-warning'],
            4 => ['estado' => 'CANCELADA', 'claseEstado' => 'bg-danger'],
            6 => ['estado' => 'AUTORIZADA', 'claseEstado' => 'badge-soft-success'],
            7 => ['estado' => 'EN SURTIDO', 'claseEstado' => 'badge-soft-secondary'],
            8 => ['estado' => 'ENTREGADA', 'claseEstado' => 'badge-soft-dark'],
            9 => ['estado' => 'PAGANDO', 'claseEstado' => 'bg-primary'],
            10 => ['estado' => 'PAGADA', 'claseEstado' => 'bg-success'],
            11 => ['estado' => 'RECHAZADA', 'claseEstado' => 'bg-danger'],
        ];

        return $estados[$estatus] ?? ['estado' => 'DESCONOCIDO', 'claseEstado' => 'bg-secondary'];
    }

    /**
     * Asigna la descripción del centro de costo
     */
    private function asignarDescripcionCC($cc){
        $rawCentrosCosto = File::get(base_path('Modules/Compras/resources/assets/json/centrosCostos/catCentrosCostos.json'));
        $jsonCC = json_decode(json: $rawCentrosCosto, associative: true);
        $dataCC = $jsonCC[$cc];
        return $dataCC;
    }

    private function getOrdenTrabajo($idSolictud){
        $ordenTrabajo = OrdenTrabajo::where('com_solicitudes_compra_id', $idSolictud)->first();
        return $ordenTrabajo->orden_trabajo ?? null;
    }
}
