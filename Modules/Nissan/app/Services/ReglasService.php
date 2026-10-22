<?php

namespace Modules\Nissan\Services;

use Modules\Nissan\Models\CatGastosPredef;

class ReglasService
{

/**
 * Campos dehabilitados por tipo de vehiculo
 */
    protected array $camposHabilitados = [
        "NU" => [
            "otros", "gasolina", "previa", "descuentos", "descuento_impulso",
            "traslados", "subsidios", "cortesia", "porcentaje_bdc",
            "comision_garantizada", "comision_garantizada_gerencia",
            "comision_garantizada_bdc", "comision_garantizada_cor_bdc"
        ],
        "US" => [
            "otros", "gasolina", "previa", "descuentos", "descuento_impulso",
            "traslados", "subsidios", "descuento_da", "accesorios", "placas",
            "porcentaje_bdc", "comision_garantizada",
            "comision_garantizada_gerencia", "comision_garantizada_bdc",
            "comision_garantizada_cor_bdc"
        ]
    ];

    /**
     * Obtiene las reglas completas (campos habilitados, valores de gastos y promociones)
     * filtradas específicamente para una agencia.
     */
    public function obtenerReglasPorAgencia($idAgencia)
    {
        $gastosDb = CatGastosPredef::where('agencia', $idAgencia)->where('activo', 1)->get();
        $valoresGastosPorTipo = $gastosDb->groupBy('tipo_vehiculo')->map(function ($grupo) {
            return $grupo->where('importe', '>', 0)
                         ->pluck('importe', 'clave_gasto')
                         ->toArray();
        });

        $reglas = [];

        foreach ($this->camposHabilitados as $tipoVehiculo => $campos) {
            $reglas[$tipoVehiculo] = [
                'campos_habilitados' => $campos,
                'valores_gastos'     => $valoresGastosPorTipo->get($tipoVehiculo, []),
            ];
        }

        return $reglas;
    }

    /**
     * Método auxiliar para obtener promociones (puedes adaptarlo si vienen de otra tabla).
     */
    private function obtenerPromocionPorAgenciaYTipo($idAgencia, $tipoVehiculo)
    {
        /*
        $promo = CatPromociones::where('agencia', $idAgencia)
                               ->where('tipo_vehiculo', $tipoVehiculo)
                               ->first();
        if ($promo) {
            return [
                'filtro_descripcion' => $promo->filtro_descripcion,
                'porcentaje' => (float) $promo->porcentaje,
            ];
        }
        */
        return null;
    }



}
