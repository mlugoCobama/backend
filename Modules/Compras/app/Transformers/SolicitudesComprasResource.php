<?php

namespace Modules\Compras\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\Compras\Models\OrdenTrabajo;

class SolicitudesComprasResource extends JsonResource
{

    // Caché local para evitar consultas repetidas
    private static $usuariosCache = [];

    private static $empresasCache = [];
    private static ?array $cacheCentrosCosto = null;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array


    {
        $usuarioDestino =  $this->getNombreUsuario( $this->tipo ,$this->usuario_destino, $this->empresa);
        $usuarioSolicita =  $this->getNombreUsuario( 1 ,$this->usuario_solicita, $this->empresa);
        $estadoInfo = $this->asignarEstado($this->estatus, $this->auto_gg, $this->auto_admin, $this->auto_macro, $this->tipo);
        $datosCC = $this->asignarDescripcionCC($this->c_c);
        $ordenTrabajo = $this->getOrdenTrabajo($this->id, $this->tipo);
        $multiUnidad = $this->isMultiple($this->tipo ,$this->usuario_destino);

        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'requiere_anticipo' => $this->requiere_anticipo,
            'motivo' => $this->motivo,
            'fecha' => $this->fecha ? Carbon::parse($this->fecha)->format('d/m/Y H:i'): null,
            'c_c' => $this->c_c,
            'centro_costo' => $datosCC['descripcion'],
            'usuario_destino' => $usuarioDestino['nombre_completo'],
            'usuario_destino_id' => $this->usuario_destino,
            'intercompania' => $this->intercompania ?? $this->empresa,
            'empresa' => $this->c_c > 0 ?  $this->setAgenciaName($this->intercompania?? $this->empresa)  :  $this->setEmpresaName($this->intercompania ?? $this->empresa),
            'usuario_solicita' => $usuarioSolicita['nombre_completo'],
            'usuario_solicita_id' => $this->usuario_solicita,
            'estatus' => $this->estatus,
            'estado' => $estadoInfo['estado'],
            'claseEstado' => $estadoInfo['claseEstado'],
            'auto_admin' => $this->auto_admin,
            'auto_gg' => $this->auto_gg,
            'auto_macro' => $this->auto_macro ?? null,
            'tipo' => $this->tipo,
            'orden_trabajo' => $ordenTrabajo ?? null,
            'id_orden_trabajo' => $this->id_orden_trabajo ?? null,
            'folio_requisicion' => $this->folio_requisicion ?? null,
            'formato_orden' => $this->formato_orden ?? null,
            'tipo_mantenimiento' => $this->tipo_mantenimiento ?? null,
            'sistema' => $this->sistema ?? null,
            'razon_cancelacion' => $this->razon_cancelacion ?? null,
            'multiUnidad' => $multiUnidad,
            'observaciones' => $this->observaciones ?? null,
            'total_orden' => (float) $this->total_orden * 1.16 ?? 0,
            'proveedor' => $this->proveedor ?? '-',
            'folio_oc' => $this->folio_oc ?? '-',
            'modo_pago' =>  $this->labelModoPago($this->modo_pago ?? null),
            'pagado' => $this->labelFlagPagado($this->pagado ?? null),
            'motivo_revision' => $this->motivo_revision ?? null,
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
            4 => ['estado' => 'CANCELADA', 'claseEstado' => 'bg-danger'],
            5 => ['estado' => 'ORDEN DE COMPRA', 'claseEstado' => 'bg-warning'],
            6 => ['estado' => 'AUTORIZADA', 'claseEstado' => 'badge-soft-success'],
            7 => ['estado' => 'AUTO. A PAGO', 'claseEstado' => 'badge-soft-primary'],
            8 => ['estado' => 'EN SURTIDO', 'claseEstado' => 'bg-primary'],
            9 => ['estado' => 'ENTREGADO', 'claseEstado' => 'badge-soft-dark'],
            10 => ['estado' => 'FACTURADO', 'claseEstado' => 'badge-soft-primary'],
            11 => ['estado' => 'PAGO SOLICITADO', 'claseEstado' => 'bg-warning'],
            12 => ['estado' => 'PAGADO', 'claseEstado' => 'bg-success'],
            13 => ['estado' => 'CARGA COMPLEMENTO', 'claseEstado' => 'bg-secondary'],
            14 => ['estado' => 'FINALIZADO', 'claseEstado' => 'bg-dark'],
            
        ];

        return $estados[$estatus] ?? ['estado' => 'DESCONOCIDO', 'claseEstado' => 'bg-secondary'];
    }

    /**
     * Asigna la descripción del centro de costo
     */
    private function asignarDescripcionCC(string $cc): ?array
    {
        if (self::$cacheCentrosCosto === null) {
            $rawCentrosCosto = File::get(
                base_path('Modules/Compras/resources/assets/json/centrosCostos/catCentrosCostos.json')
            );
                self::$cacheCentrosCosto = json_decode($rawCentrosCosto, true);
        }
        return self::$cacheCentrosCosto[$cc] ?? null;
    }

    private function getOrdenTrabajo($idSolictud, $tipoSolicitud){

        if($tipoSolicitud == 2){
            $ordenTrabajo = OrdenTrabajo::where('com_solicitudes_compra_id', $idSolictud)->first();
        return $ordenTrabajo->orden_trabajo ?? null;
        }

        return null;
    }

    private function isMultiple($tipo ,$usuario_destino){
        if($tipo == 2 && $usuario_destino == 602) {
            return true;
        }
        return false;
    }

    private function labelFlagPagado($value)
    {
        $labels = [
            0 => 'PENDIENTE DE PAGO',
            1 => 'PAGADO',
            2 => 'PAGADO PARCIALMENTE'
        ];
        return $labels[$value] ?? null;
    }

    private function labelModoPago($value)
    {
        $labels = [
            2 => 'CREDITO',
            1 => 'CONTADO'
        ];
        return $labels[$value] ?? null;
    }

    private function setAgenciaName($intercompania){
        $empresas = [710 => 'NISSAN UNIVERSIDAD', 7051 => 'NISSAN AZCAPOTZALCO', 
            712 => 'NISSAN CAMPESTRE', 700 => 'CORPORATIVO AUTOS SONI',
            240 => 'SERVIGAS DEL VALLE', 7064 => 'RENAULT AZCAPOTZALCO',
            7062 => 'RENAULT ECATEPEC', 7063 => 'RENAULT VALLEJO', 7061 => 'RENAULT PACHUCA',
        ];

        return  $empresas[$intercompania] ?? 'Agencia default';
    }

    private function setEmpresaName($intercompania)
    {
        if (isset(self::$empresasCache[$intercompania])) {
            return self::$empresasCache[$intercompania];
        }
        $empresas = DB::connection('intranet')->select('CALL SP_GetEmpresas()');

        foreach ($empresas as $empresa) {
            self::$empresasCache[$empresa->intercompania] = $empresa->name;
        }

        return self::$empresasCache[$intercompania] ?? null;
    }



    
}
