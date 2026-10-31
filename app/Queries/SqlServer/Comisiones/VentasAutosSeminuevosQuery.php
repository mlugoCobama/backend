<?php

namespace App\Queries\SqlServer\Comisiones;

use Illuminate\Support\Facades\DB;

class VentasAutosSeminuevosQuery
{
    public function getVentasSeminuevos($conexion, $fechaInicio, $fechaFin){
        return DB::connection($conexion)->select("
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
            CAST(fa.faau_fecha AS DATE) AS fecha_salida,
            fa.faau_iva,
            fa.faau_total,
            (fa.faau_total - (fa.faau_iva + fa.faau_isan)) as Venta,
            (vi.vehi_CostoOperacion + vi.vehi_CostoEquipo + vi.vehi_CostoGastos) AS Costo,
            (((fa.faau_total - (fa.faau_iva + fa.faau_isan)) ) - (vi.vehi_CostoOperacion + vi.vehi_CostoEquipo + vi.vehi_CostoGastos ))  as Utilidad
        FROM
            [SISTEMAS].[dbo].[Vt_InventarioAutos] vi
        JOIN [SISTEMAS].[dbo].[Vt_FacturaAutos] fa
            ON fa.faau_vehi_vehiculoid = vi.vehi_vehiculoid
        JOIN [SISTEMAS].[dbo].[Vt_Modelos] mo
            ON mo.mode_modeloid = vi.vehi_mode_modeloid

        WHERE
            vi.vehi_clas_clave = 'US'
        AND
            CAST(fa.faau_fecha AS DATE) BETWEEN ? AND ?
        AND
            fa.faau_fechacancelacion IS NULL",
            [$fechaInicio, $fechaFin]
        );
    }
}
