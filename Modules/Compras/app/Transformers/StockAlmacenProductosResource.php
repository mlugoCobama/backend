<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class StockAlmacenProductosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $stock = (float)$this->stock;
        $minimo = (float)$this->stock_minimo;
        $empresa = $this->setEmpresaName($this->almacen_empresa);
        $acronimo = $this->generarAbreviatura($empresa);

        return [
            'id' => $this->id ?? null,
            'stock' => $stock,
            'minimo' => $minimo,
            'alertaMinimo' => $stock <= $minimo,
            "alamacen_empresa" => $this->almacen_empresa  ?? null,
            "empresa"  => $empresa,
            "producto_id" => $this->com_productos_id,
            'clave_interna' => $this->producto->clave_interna ?? $acronimo.'-'.$this->clave_interna,
            'descripcion_planta' => $this->producto->descripcion_planta ?? $this->descripcion_planta,
            'descripcion_proveedor' => $this->producto->descripcion_proveedor ?? $this->descripcion_proveedor,
            'unidad_medida_id' => $this->producto->unidadMedida->id ?? $this->unidad_medida_id,
            'unidad_medida' => $this->producto->unidadMedida->nombre ?? $this->unidad_medida,
            'subcategoria' => $this->producto->subCategoria->nombre ?? $this->subcategoria,
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
    private static $acronimosCache = [];

public function generarAbreviatura($texto)
{
    $texto = trim($texto);
    if (isset(self::$acronimosCache[$texto])) {
        return self::$acronimosCache[$texto];
    }
    $palabrasArray = preg_split('/\s+/', $texto);
    $palabrasValidas = [];
    foreach ($palabrasArray as $palabra) {
        if (!in_array(mb_strtolower($palabra), ['de', 'del', 'el', 'la', 'y', 'en'])) {
            $palabrasValidas[] = $palabra;
        }
    }
    $cantidad = count($palabrasValidas);
    $acronimo = '';
    if ($cantidad >= 3) {
        for ($i = 0; $i < 3; $i++) {
            $acronimo .= mb_strtoupper(mb_substr($palabrasValidas[$i], 0, 1));
        }
    } elseif ($cantidad == 2) {
        $acronimo .= mb_strtoupper(mb_substr($palabrasValidas[0], 0, 2));
        $acronimo .= mb_strtoupper(mb_substr($palabrasValidas[1], 0, 1));
    } elseif ($cantidad == 1) {
        $acronimo .= mb_strtoupper(mb_substr($palabrasValidas[0], 0, 3));
    } else {
        $acronimo = 'N/A';
    }
    return self::$acronimosCache[$texto] = $acronimo;
}

}
