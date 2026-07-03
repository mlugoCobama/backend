<?php

namespace Modules\Compras\Transformers;

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
        return [
            "id" => $this->id,
            "proveedor" => $this->proveedor,
            "num_tag" => $this->num_tag,
            "numero_cuenta" => $this->numero_cuenta,
            "serie" => $this->serie,
            "fecha_alta" => $this->fecha_alta,
            "fecha_venciemiento" => $this->fecha_venciemiento,
            "saldo_actual" => $this->saldo_actual,
            'esatus' => $this->estatus,
            "estado" => EstatusAsigancionTarjetas::label($this->estatus),
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
}
