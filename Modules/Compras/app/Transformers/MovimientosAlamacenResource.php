<?php

namespace Modules\Compras\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class MovimientosAlamacenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * return MovimientosAlmacen::with(['almacenProducto.producto.unidadMedida'])
     */
    public function toArray(Request $request): array
    {
        $empresa = $this->setEmpresaName($this->almacenProducto->almacen_empresa);

        return [
            'id_movimiento' => $this->id,
            'cantidad' => $this->cantidad,
            'tipo' => $this->tipo,
            'almacen_producto_id' => $this->com_almacen_producto_id,
            'observaciones' => $this->observaciones,
            'fecha' => $this->fecha,
            'com_almacen_id' => $this->com_almacen_id,
            'usuario_entrega' => $this->id_usuario_entrega,
            'id_salida' => $this->com_id_entrega_tecnico,
            'almacen_empresa' => $this->almacenProducto->almacen_empresa,
            'empresa'  => $empresa,
            'clave_interna' => $this->almacenProducto->producto->clave_interna,
            'descripcion_planta' => $this->almacenProducto->producto->descripcion_planta,
            'descripcion_proveedor' => $this->almacenProducto->producto->descripcion_proveedor,
            'unidad_medida_id'=> $this->almacenProducto->producto->unidadMedida->id,
            'unidad_medida' => $this->almacenProducto->producto->unidadmedida->nombre,
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
