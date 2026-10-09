<?php

namespace Modules\ParqueVehicular\Transformers;

use App\Enums\EstatusOrdenesServicio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ProveedorVehiculoResource extends JsonResource
{
    private static $empresasCache = [];
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folio' => $this->folio ?? 'Sin Folio',
            'vehiculos_id' => $this->com_datos_vehiculos_id,
            'proveedores_id' => $this->com_proveedores_id ?? null,
            'marca' => $this->vehiculo?->marca ?? '',
            'submarca' =>$this->vehiculo?->submarca ?? '',
            'modelo' =>$this->vehiculo?->modelo ?? '',
            'no_serie' => $this->vehiculo?->no_serie ?? 'S/N',
            'eco' => $this->vehiculo?->nro_economico ?? 'S/N',
            'proveedor' => $this->proveedorAsignado?->proveedor?->nombre ?? null,
            'estatus' => $this->estatus,
            'estatus_txt' => EstatusOrdenesServicio::label($this->estatus),
            'estatus_class' => EstatusOrdenesServicio::colorBS($this->estatus),
            'observaciones' => $this->observaciones,
            'placas' => $this->vehiculo?->placas ?? 'S/N',
            'falla' => $this->descripcion_falla ?? 'No especifiacada',
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'empresa' => $this->setEmpresaName($this->intercompania),

        ];
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

        return self::$empresasCache[$intercompania] ?? 'No Especificada';
    }
}
