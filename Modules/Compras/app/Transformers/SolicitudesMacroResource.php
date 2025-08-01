<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Compras\Models\OrdenTrabajo;

class SolicitudesMacroResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    
    {
        $usuarioSolicita =  $this->getNombreUsuario($this->usuario_solicita);
        $estadoInfo = $this->asignarEstado($this->estatus);
        // $datosUnidad = $this->getUnidad($this->usuario_destino);
        $datosCC = $this->asignarDescripcionCC($this->c_c);
        $claseEmpresa = $this->asignarBadges($this->id_suc);
        return [
            'id' => $this->id_solicitud,
            'folio' => $this->folio,
            'motivo' => $this->motivo,
            'fecha' => $this->fecha,
            'c_c' => $this->c_c,
            'centro_costo' => $datosCC['descripcion'],
            'usuario_destino' => "ECO: $this->id MOD: $this->marca_vehiculo $this->submarca $this->modelo PLACAS: $this->placas",
            'empresa' => $this->empresa,
            'usuario_solicita' =>  $usuarioSolicita['nombre_completo'],
            'estatus' => $this->estatus,
            'estado' => $estadoInfo['estado'],
            'claseEstado' => $estadoInfo['claseEstado'],
            'claseEmpresa' => $claseEmpresa['clase'],
            'auto_admin' => $this->auto_admin,
            'auto_gg' => $this->auto_gg,
            'auto_macro' => $this->auto_macro,
            'tipo' => $this->tipo,
            'id_orden_trabajo' => $this->id_orden_trabajo,
            'orden_trabajo' => $this->orden_trabajo,
            'tipo_mantenimiento' => $this->tipo_mantenimiento,
            'sistema' => $this->sistema,
            // 'detalle' => DetalleSolicitudCompraResource::collection($this->DetallesSolicitud)
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
            ];
        }
        return ['nombre_completo' => 'No asignado', 'empresa' => null];
    }

    private function getUnidad($unidadId){

        $unidad = DatosVehiculo::where('id', $unidadId)->first();
        return "ECO: $unidad->id  $unidad->marca $unidad->submarca $unidad->submarca $unidad->modelo PLACAS: $unidad->placas";
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
    private function asignarDescripcionCC($cc){
        $rawCentrosCosto = File::get(base_path('Modules/Compras/resources/assets/json/centrosCostos/catCentrosCostos.json'));
        $jsonCC = json_decode(json: $rawCentrosCosto, associative: true);
        $dataCC = $jsonCC[$cc];
        return $dataCC;
    }

}