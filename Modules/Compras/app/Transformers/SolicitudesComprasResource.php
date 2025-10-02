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
        $estadoInfo = $this->asignarEstado($this->estatus, $this->auto_gg, $this->auto_admin, $this->auto_macro, $this->tipo);
        $datosCC = $this->asignarDescripcionCC($this->c_c);
        $ordenTrabajo = $this->getOrdenTrabajo($this->id);
        $multiUnidad = $this->isMultiple($this->tipo ,$this->usuario_destino);

        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'motivo' => $this->motivo,
            'fecha' => $this->fecha,
            'c_c' => $this->c_c,
            'centro_costo' => $datosCC['descripcion'],
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
            'orden_trabajo' => $ordenTrabajo,
            'id_orden_trabajo' => $this->id_orden_trabajo ?? null,
            'folio_requisicion' => $this->folio_requisicion ?? null,
            'formato_orden' => $this->formato_orden ?? null,
            'tipo_mantenimiento' => $this->tipo_mantenimiento ?? null,
            'sistema' => $this->sistema ?? null,
            'razon_cancelacion' => $this->razon_cancelacion ?? null,
            'multiUnidad' => $multiUnidad
        ];
    }

    /**
     *  Recupera los datos del usuario para asignarlos a la consulta
     */
    private function getNombreUsuario($tipoSolicitud , $usuarioId, $empresa)
    {
        if($tipoSolicitud == 2){
             $usuario = UsersResource::collection(DB::connection('dashboard')->select('call SistemaTickets.SP_GetDataAutotanque('. $usuarioId.','.$empresa.')'));
             $nombreCompleto = trim($usuario[0]->firstname . ' ' . $usuario[0]->realname. ' No. Serie:'.$usuario[0]->name );
        }else{
            $usuario = UsersResource::collection(DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' .  $usuarioId . ')'));
            $nombreCompleto = trim($usuario[0]->firstname . ' ' . $usuario[0]->realname);
        }

        if (count($usuario) > 0) {
            return [
                'nombre_completo' => $nombreCompleto,
                'empresa' => $usuario[0]->empresa ?? null,
            ];
        }
        return ['nombre_completo' => 'No asignado', 'empresa' => null];
    }

    /**
     * Asigna los datos del status como una etiqueta y una clase css
     */
    private function asignarEstado($estatus, $gg, $admin, $macro, $tipo )
    {
        $label = ($gg == 1  && $admin == 1 && $tipo == 2 ) ? "ESP. AUT. MACRO" : "ESP. AUT. PLANTA";
        $label2 = ($gg == 1 && $admin == 1  && $macro == 0 && $tipo == 2) ? "ESP. AUT. MACRO" : "EN COTIZACIÓN";
        $estados = [
            1 => ['estado' => $label, 'claseEstado' => 'badge-soft-info'],
            2 => ['estado' => 'SOLICITADO', 'claseEstado' => 'bg-info'],
            3 => ['estado' => $label2, 'claseEstado' => 'badge-soft-warning'],
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

    private function isMultiple($tipo ,$usuario_destino){
        if($tipo == 2 && $usuario_destino == 602) {
            return true;
        }
        return false;
    }
}
