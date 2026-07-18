<?php

namespace Modules\Compras\Transformers;

use App\Enums\EstatusActivos;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\EstatusAsigancionTarjetas;
use Illuminate\Support\Facades\DB;

class TagsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $empresaName = $this->setEmpresaName($this->intercompania);
        $marca = $this->setMarca($this->proveedor);
        return [
            "id" => $this->id,
            "proveedor" => $this->proveedor,
            "marca" => $marca,
            "num_tag" => $this->num_tag,
            "numero_cuenta" => $this->numero_cuenta,
            "serie" => $this->serie,
            "fecha_alta" => $this->fecha_alta  ?? null,
            "fecha_venciemiento" => $this->fecha_venciemiento ?? null,
            "saldo_actual" => $this->saldo_actual  ?? null,
            'esatus' => $this->estatus,
            "estado" => EstatusActivos::label($this->estatus),
            "observaciones" => $this->observaciones,
            "intercompania" => $this->intercompania ?? 'NO DISPONIBLE', 
            "empresa" => $empresaName, 
        ];
    }

    private static $empresasCache = [];
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

    private function setMarca($idMarca){
        return match ($idMarca) {
            '1' => 'PASE',
            '2' => 'IAVE',
            '3' => 'TeleVia',
            '4' => 'ViaPass',
            '5' => 'EasyTrip',
            '6' => 'Otro',
            default => 'Otro'
        };
    }
}
