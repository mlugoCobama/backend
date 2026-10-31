<?php

namespace App\Queries\SqlServer\Comisiones;

use Illuminate\Support\Facades\DB;

class VentasAutosNuevosQuery
{

    public function getVentasNuevos($conexion, $fechaInicial, $fechaFinal){
        $libroVentas = DB::connection($conexion)->select(
            " WITH BonificacionesAgrupadas AS (
                SELECT
                    prim_documento,
                    SUM(prim_importe) AS total_prim_importe
                FROM [SISTEMAS].[dbo].[vt_PrimBonif]
                GROUP BY prim_documento
            )
            SELECT
                CAST(fa.faau_fecha AS DATE) AS fecha_factura,
                CAST(fa.faau_fechacancelacion AS DATE) AS fecha_cancelacion,
                fa.faau_vend_clave,
                fa.faau_nofactura,
                fa.faau_razonfactura,
                fa.faau_idagencia AS id_agencia,
                vi.vehi_clas_clave,
                vi.vehi_anio,
                vi.vehi_numeroinventario,
                vi.vehi_serie,
                mo.mode_clave,
                mo.mode_descripcion,
                fa.faau_form_TipoVenta,
                fa.faau_iva,
                fa.faau_total,
                (ISNULL(bo.total_prim_importe, 0) / 1.16) AS bonificacion,
                (fa.faau_total - (fa.faau_iva + fa.faau_isan)) as Venta,
                (vi.vehi_CostoOperacion + vi.vehi_CostoEquipo + vi.vehi_CostoGastos) AS Costo,
                -- (((fa.faau_total - (fa.faau_iva + fa.faau_isan)) ) - (vi.vehi_CostoOperacion + vi.vehi_CostoEquipo + vi.vehi_CostoGastos ))  as Utilidad
                (((fa.faau_total - (fa.faau_iva + fa.faau_isan))) - (vi.vehi_CostoOperacion + vi.vehi_CostoEquipo + vi.vehi_CostoGastos)) + (ISNULL(bo.total_prim_importe, 0) / 1.16) AS Utilidad
            FROM
                [SISTEMAS].[dbo].[Vt_InventarioAutos] vi
            JOIN [SISTEMAS].[dbo].[Vt_FacturaAutos] fa
                ON fa.faau_vehi_vehiculoid = vi.vehi_vehiculoid
            JOIN [SISTEMAS].[dbo].[Vt_Modelos] mo
                ON mo.mode_modeloid = vi.vehi_mode_modeloid
            -- JOIN [SISTEMAS].[dbo].[vt_PrimBonif] bo
                -- ON bo.prim_documento = fa.faau_nofactura
            LEFT JOIN BonificacionesAgrupadas bo
                ON bo.prim_documento = fa.faau_nofactura
            WHERE
                vi.vehi_clas_clave = 'NU'
            AND
                CAST(fa.faau_fecha AS DATE) BETWEEN ? AND ?
            AND
                fa.faau_fechacancelacion IS NULL
            ORDER BY fa.faau_nofactura",
                [$fechaInicial, $fechaFinal]
            );

            return $libroVentas;
    }
}
