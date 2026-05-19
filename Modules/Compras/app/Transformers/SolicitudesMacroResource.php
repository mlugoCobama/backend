<?php

namespace Modules\Compras\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Compras\Models\OrdenTrabajo;

class SolicitudesMacroResource extends JsonResource
{
    private static $usuariosCache = [];
    private static $empresasCache = [];
    private static ?array $cacheCentrosCosto = null;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    
    {
        $usuarioSolicita =  $this->getNombreUsuario($this->usuario_solicita);
        $estadoInfo = $this->asignarEstado($this->estatus, $this->auto_admin, $this->auto_gg, $this->auto_macro);
        // $datosUnidad = $this->getUnidad($this->usuario_destino);
        $datosCC = $this->asignarDescripcionCC($this->c_c);
        $multiUnidad = $this->isMultiple($this->tipo, $this->id);
        // $claseEmpresa = $this->asignarBadges($this->id_suc);
        return [
            'id' => $this->id_solicitud,
            'folio' => $this->folio,
            'requiere_anticipo' => $this->requiere_anticipo,
            'motivo' => $this->motivo,
            'fecha' => $this->fecha ? Carbon::parse($this->fecha)->format('d/m/Y H:i'): null,
            'c_c' => $this->c_c,
            'eco' => "ECO: $this->eco" ?? "ECO: 0",
            'serie' => "No. SERIE: $this->no_serie" ?? "No. SERIE: N/D",
            'centro_costo' => $datosCC['descripcion'],
            'usuario_destino' => "ECO: $this->eco MOD: $this->marca_vehiculo $this->submarca $this->modelo PLACAS: $this->placas No. SERIE: $this->no_serie",
            'usuario_destino_id' => $this->id,
            'intercompania' => $this->intercompania ?? null,
            'empresa' => $this->setEmpresaName($this->intercompania),
            'usuario_solicita' =>  $usuarioSolicita['nombre_completo'],
            'usuario_solicita_id' => $this->usuario_solicita,
            'estatus' => $this->estatus,
            'estado' => $estadoInfo['estado'],
            'claseEstado' => $estadoInfo['claseEstado'],
            // 'claseEmpresa' => $claseEmpresa['clase'],
            'auto_admin' => $this->auto_admin,
            'auto_gg' => $this->auto_gg,
            'auto_macro' => $this->auto_macro,
            'tipo' => $this->tipo,
            'id_orden_trabajo' => $this->id_orden_trabajo,
            'orden_trabajo' => $this->orden_trabajo,
            'folio_requisicion' => $this->folio_requisicion,
            'formato_orden' => $this->formato_orden,
            'tipo_mantenimiento' => $this->tipo_mantenimiento,
            'sistema' => $this->sistema,
            'razon_cancelacion' => $this->razon_cancelacion ?? null,
            'multiUnidad' => $multiUnidad,
            'observaciones' => $this->observaciones ??  null,
            'total_orden' => (float) $this->total_orden ?? 0,
            'proveedor' => $this->proveedor ?? '-',
            'folio_oc' => $this->folio_oc ?? '-',
            'modo_pago' =>  $this->labelModoPago($this->modo_pago ?? null),
            'pagado' => $this->labelFlagPagado($this->pagado ?? null),
            'motivo_revision' => $this->motivo_revision ?? null,
            'surtido' => $this->surtido ?? 0,
            'surtido_solicitado' => $this->surtido_solcitado ? true : false,
            'facturas_xml' => $this->facturas_xml ?? 0,
            'comprobantes_pago' => $this->comprobantes_pago ?? 0,
            'complementos_pago' => $this->complementos_pago ?? 0,
            'total_facturas' => $this->total_facturas ?? 0,
            'vencido' => !Carbon::parse($this->fecha_entrega)->endOfDay()->isPast(),
            // 'detalle' => DetalleSolicitudCompraResource::collection($this->DetallesSolicitud)
        ];
    }

    /**
     *  Recupera los datos del usuario para asignarlos a la consulta
     */
    private function getNombreUsuario($usuarioId)
    {
        if (isset(self::$usuariosCache[$usuarioId])) {
            return self::$usuariosCache[$usuarioId];
        }

        $usuario = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $usuarioId . ')');

        if (count($usuario) > 0) {
            $resultado = [
                'nombre_completo' => trim($usuario[0]->firstname . ' ' . $usuario[0]->realname),
                'empresa' => $usuario[0]->empresa ?? null,
            ];
        } else {
            $resultado = [
                'nombre_completo' => 'No asignado',
                'empresa' => null,
            ];
        }
        self::$usuariosCache[$usuarioId] = $resultado;
        return $resultado;
    }



    private function getUnidad($unidadId){

        $unidad = DatosVehiculo::where('id', $unidadId)->first();
        return "ECO: $unidad->id  $unidad->marca $unidad->submarca $unidad->submarca $unidad->modelo PLACAS: $unidad->placas";
    }
    /**
     * Asigna los datos del status como una etiqueta y una clase css
     */
    private function asignarEstado($estatus, $admin, $gg, $macro )
    {
        $label = ($gg == 1  && $admin == 1 ) ? "ESP. AUT. MACRO" : "ESP. AUT. PLANTA";
        $label2 = ($gg == 1 && $admin == 1  && $macro == 0) ? "ESP. AUT. MACRO" : "EN COTIZACIÓN";

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
            10 => ['estado' => 'POR FACTURAR', 'claseEstado' => 'badge-soft-primary'],
            11 => ['estado' => 'PAGO SOLICITADO', 'claseEstado' => 'bg-warning'],
            12 => ['estado' => 'PAGADO', 'claseEstado' => 'bg-success'],
            13 => ['estado' => 'CARGA COMPLEMENTO', 'claseEstado' => 'bg-secondary'],
            14 => ['estado' => 'FINALIZADO', 'claseEstado' => 'bg-dark'],
        ];

        return $estados[$estatus] ?? ['estado' => 'DESCONOCIDO', 'claseEstado' => 'bg-secondary'];
    }

    private function asignarBadges($entidad)
    {
        $estados = [
            1 =>  ['clase' => 'badge bg-primary' ],
            2 =>  ['clase' => 'badge bg-secondary' ],
            3 =>  ['clase' => 'badge bg-success' ],
            5 =>  ['clase' => 'badge bg-danger' ],
            4 =>  ['clase' => 'badge bg-warning text-dark' ],
            6 =>  ['clase' => 'badge bg-info text-dark' ],
            7 =>  ['clase' => 'badge bg-light text-dark' ],
            8 =>  ['clase' => 'badge bg-dark' ],
            9 =>  ['clase' => 'badge rounded-pill bg-success' ],
            10 => ['clase' => 'badge rounded-pill bg-primary' ],
            11 => ['clase' => 'badge rounded-pill bg-warning text-dark' ],
            12 => ['clase' => 'badge text-bg-primary border border-light' ],
            13 => ['clase' => 'badge text-bg-success border border-dark' ],
            14 => ['clase' => 'badge text-bg-info border border-primary' ],
            15 => ['clase' => 'badge bg-transparent text-primary border border-primary' ],
            16 => ['clase' => 'badge bg-transparent text-success border border-success' ],
            17 => ['clase' => 'badge bg-transparent text-danger border border-danger' ],
            18 => ['clase' => 'badge badge rounded-pill bg-dark text-warning' ],
        ];

        return $estados[$entidad] ?? ['clase' => 'badge badge rounded-pill bg-secondary'];
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


    private function isMultiple($tipo ,$usuario_destino){
        if($tipo == 2 && $usuario_destino == 602) {
            return true;
        }
        return false;
    }

    private function labelFlagPagado($value) {
        $labels = [
            0 => 'PENDIENTE DE PAGO',
            1 => 'PAGADO',
            2 => 'PAGADO PARCIALMENTE',
        ];
        return $labels[$value] ?? null;
    }

    private function labelModoPago($value) {
    $labels = [
        2 => 'CREDITO',
        1 => 'CONTADO'
    ];
    return $labels[$value] ?? null;
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