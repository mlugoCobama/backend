<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CorteService
{
public function generar($fechaInicio, $fechaFin, $claveCorte, $agencia)
{
    return DB::transaction(function () use ($fechaInicio, $fechaFin, $claveCorte, $agencia) {

        // 1. Crear corte
        $corteId = DB::connection('autos')->table('com_corte')->insertGetId([
            'fecha_corte' => now(),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'clave_corte' => $claveCorte,
            'created_at' => now()
        ]);

        // 2. Obtener TODAS las comisiones autorizadas
        $comisiones = collect(DB::connection('autos')->select("

            -- FINANCIAMIENTO
            SELECT id, com_vendedores_id as vendedor_id,
                   comision_asesor_pesos as comision,
                   monto_financiar as monto_venta,
                   'financiamiento' as origen
            FROM com_financiamiento
            WHERE estatus = 2  AND agencia = $agencia

            UNION ALL

            -- SEGUROS
            SELECT id, com_vendedores_id,
                   comision_apv_pesos,
                   prima_neta,
                   'seguros'
            FROM com_seguro
            WHERE estatus = 2   AND agencia = $agencia

            UNION ALL

            -- ACCESORIOS
            SELECT id, com_vendedores_id,
                   comision_apv_pesos,
                   sub_total_factura,
                   'accesorios'
            FROM com_accesorios
            WHERE estatus = 2 AND agencia = $agencia

            UNION ALL

            -- NUEVOS
            SELECT cgv.id, cdv.id_vendedor as vendedor_id,
                   cgv.comision_apv_pesos,
                   (cdv.utilidad_inicial - (cgv.otros + cgv.gasolina + cgv.previa + cgv.descuentos +
                    cgv.traslados + cgv.descuento_impulso + cgv.total_subsidios +
                    cgv.descuento_gastos + cgv.cortesia + cgv.accesorios + cgv.placas)),
                   'nuevos'
            FROM com_datos_venta cdv
            INNER JOIN com_gastos_venta cgv ON cgv.id_datos_venta = cdv.id
            WHERE cdv.clave_producto = 'nu'
              AND cdv.estatus = 4  AND agencia = $agencia

            UNION ALL

            -- SEMINUEVOS
            SELECT cgv.id, cdv.id_vendedor,
                   cgv.comision_apv_pesos,
                   (cdv.utilidad_inicial - (cgv.otros + cgv.gasolina + cgv.previa + cgv.descuentos +
                    cgv.traslados + cgv.descuento_impulso + cgv.total_subsidios +
                    cgv.descuento_gastos + cgv.cortesia + cgv.accesorios + cgv.placas)),
                   'seminuevos'
            FROM com_datos_venta cdv
            INNER JOIN com_gastos_venta cgv ON cgv.id_datos_venta = cdv.id
            WHERE cdv.clave_producto = 'us'
              AND cdv.estatus = 4 AND agencia = $agencia

            UNION ALL

            -- TOMA DE UNIDAD
            SELECT id, com_vendedores_id,
                   comision_apv_pesos,
                   0 as monto_venta,
                   'toma_unidad'
            FROM com_toma_unidad
            WHERE estatus = 2 AND agencia = $agencia

        "));

        if ($comisiones->isEmpty()) {
            throw new \Exception("No hay comisiones autorizadas");
        }

        // 3. Agrupar por vendedor
        $porVendedor = $comisiones->groupBy('vendedor_id');

        foreach ($porVendedor as $vendedorId => $items) {

            $totalComision = $items->sum('comision');
            $totalVenta = $items->sum('monto_venta');

            // 4. Totales por rubro
            $totalesPorRubro = [
                'nuevos' => 0,
                'seminuevos' => 0,
                'financiamiento' => 0,
                'seguros' => 0,
                'accesorios' => 0,
                'toma_unidad' => 0,
            ];

            foreach ($items as $item) {
                if (isset($totalesPorRubro[$item->origen])) {
                    $totalesPorRubro[$item->origen] += $item->comision;
                }
            }

            // 5. Insertar vendedor corte
            $vendedorCorteId = DB::connection('autos')->table('com_vendedores_corte')->insertGetId([
                'com_corte_id' => $corteId,
                'com_vendedores_id' => $vendedorId,

                'total_comisiones' => $totalComision,
                // 'total_ventas' => $totalVenta,

                'total_nuevos' => $totalesPorRubro['nuevos'],
                'total_seminuevos' => $totalesPorRubro['seminuevos'],
                'total_financiamiento' => $totalesPorRubro['financiamiento'],
                'total_seguros' => $totalesPorRubro['seguros'],
                'total_accesorios' => $totalesPorRubro['accesorios'],
                'total_toma_unidad' => $totalesPorRubro['toma_unidad'],

                'created_at' => now()
            ]);

            // 6. Insertar detalle + actualizar origen
            foreach ($items as $item) {

                DB::connection('autos')->table('com_detalle_corte')->insert([
                    'com_vendedores_corte_id' => $vendedorCorteId,
                    'origen_tipo' => $item->origen,
                    'origen_id' => $item->id,
                    'monto_comision' => $item->comision,
                    'monto_venta' => $item->monto_venta,
                    'fecha' => now(),
                    'created_at' => now()
                ]);

                // actualizar tablas origen
                $this->marcarComoPagado($item, $corteId);
            }
        }

        return $corteId;
    });
}

    private function marcarComoPagado($item, $corteId)
    {
        $tabla = match ($item->origen) {
            'nuevos' => 'com_datos_venta',
            'seminuevos' => 'com_datos_venta',
            'financiamiento' => 'com_financiamiento',
            'seguros' => 'com_seguro',
            'accesorios' => 'com_accesorios',
            'toma_unidad' => 'com_toma_unidad',
            default =>  null
        };

        $estatus = match ($item->origen) {
            'nuevos' => 5,
            'seminuevos' => 5,
            'financiamiento' => 3,
            'seguros' => 3,
            'accesorios' => 3,
            'toma_unidad' => 3,
            default =>  null
        };


        if($tabla && $estatus){
            DB::connection('autos')->table($tabla)
            ->where('id', $item->id)
            ->update([
                'estatus' => $estatus,
                // 'corte_id' => $corteId,
                // 'fecha_pago' => now()
            ]);
        }
        
    }

    public function preview($fechaInicio, $fechaFin)
{
    // 1. Obtener comisiones autorizadas
    $comisiones = collect(DB::connection('autos')->select("
        SELECT f.id, f.com_vendedores_id as vendedor_id, v.nombre,
               f.comision_asesor_pesos as comision,
               f.monto_financiar as monto_venta,
               'financiamiento' as origen
        FROM com_financiamiento f
        JOIN com_vendedores v ON v.id = f.com_vendedores_id
        WHERE f.estatus = 2 AND f.corte_id IS NULL

        UNION ALL

        SELECT s.id, s.com_vendedores_id, v.nombre,
               s.comision_apv_pesos,
               s.prima_neta,
               'seguro'
        FROM com_seguro s
        JOIN com_vendedores v ON v.id = s.com_vendedores_id
        WHERE s.estatus = 2 AND s.corte_id IS NULL

        UNION ALL

        SELECT a.id, a.com_vendedores_id, v.nombre,
               a.comision_apv_pesos,
               a.sub_total_factura,
               'accesorio'
        FROM com_accesorios a
        JOIN com_vendedores v ON v.id = a.com_vendedores_id
        WHERE a.estatus = 2 AND a.corte_id IS NULL
    "));

    if ($comisiones->isEmpty()) {
        return [
            'total_comisiones' => 0,
            'total_ventas' => 0,
            'vendedores' => []
        ];
    }

    // 2. Agrupar por vendedor
    $porVendedor = $comisiones->groupBy('vendedor_id');

    $resultado = [];
    $totalGeneralComisiones = 0;
    $totalGeneralVentas = 0;

    foreach ($porVendedor as $vendedorId => $items) {

        $totalComision = $items->sum('comision');
        $totalVenta = $items->sum('monto_venta');

        $totalGeneralComisiones += $totalComision;
        $totalGeneralVentas += $totalVenta;

        $resultado[] = [
            'vendedor_id' => $vendedorId,
            'nombre' => $items->first()->nombre,
            'total_comision' => $totalComision,
            'total_venta' => $totalVenta,
            'detalle' => $items->map(function ($item) {
                return [
                    'origen' => $item->origen,
                    'origen_id' => $item->id,
                    'comision' => $item->comision,
                    'venta' => $item->monto_venta
                ];
            })->values()
        ];
    }

    return [
        'total_comisiones' => $totalGeneralComisiones,
        'total_ventas' => $totalGeneralVentas,
        'vendedores' => $resultado
    ];
}

public function obtenerDisponibles()
{
    return collect(DB::select("
        SELECT f.id, f.com_vendedores_id as vendedor_id, v.nombre,
               f.comision_asesor_pesos as comision,
               f.monto_financiar as monto_venta,
               'financiamiento' as origen
        FROM com_financiamiento f
        JOIN com_vendedores v ON v.id = f.com_vendedores_id
        WHERE f.estatus = 2 AND f.corte_id IS NULL

        UNION ALL

        SELECT s.id, s.com_vendedores_id, v.nombre,
               s.comision_apv_pesos,
               s.prima_neta,
               'seguro'
        FROM com_seguro s
        JOIN com_vendedores v ON v.id = s.com_vendedores_id
        WHERE s.estatus = 2 AND s.corte_id IS NULL

        UNION ALL

        SELECT a.id, a.com_vendedores_id, v.nombre,
               a.comision_apv_pesos,
               a.sub_total_factura,
               'accesorio'
        FROM com_accesorios a
        JOIN com_vendedores v ON v.id = a.com_vendedores_id
        WHERE a.estatus = 2 AND a.corte_id IS NULL
    "));


    
}

public function comisionesAutorizadasNuevos($idVendedor){
    return DB::connection('autos')->select(
        "SELECT 
        cgv.id,
        cdv.estatus,
        cgv.comision_apv_pesos AS comision_apv,
        'Sin observaciones' AS observaciones,
        CONCAT(cdv.no_factura, '-', cdv.descripcion,'-',cdv.clave_producto,'-',cdv.no_inventario ) AS descripcion,
        (cdv.utilidad_inicial - (cgv.otros + cgv.gasolina + cgv.previa + cgv.descuentos + cgv.traslados + cgv.descuento_impulso + cgv.total_subsidios + cgv.descuento_gastos + cgv.cortesia + cgv.accesorios + cgv.placas)) AS importe_venta       
        FROM com_datos_venta cdv 
        INNER JOIN com_gastos_venta cgv ON cgv.id_datos_venta = cdv.id
        WHERE cdv.clave_producto = 'nu' and cdv.estatus = 4 and id_vendedor = $idVendedor");
}

public function comisionesAutorizadasSeminuevos($idVendedor){
    return DB::connection('autos')->select(
        "SELECT 
        cgv.id,
        cdv.estatus,
        cgv.comision_apv_pesos AS comision_apv,
        'Sin observaciones' AS observaciones,
        CONCAT(cdv.no_factura, '-', cdv.descripcion,'-',cdv.clave_producto,'-',cdv.no_inventario ) AS descripcion,
        (cdv.utilidad_inicial - (cgv.otros + cgv.gasolina + cgv.previa + cgv.descuentos + cgv.traslados + cgv.descuento_impulso + cgv.total_subsidios + cgv.descuento_gastos + cgv.cortesia + cgv.accesorios + cgv.placas)) AS importe_venta       
        FROM com_datos_venta cdv 
        INNER JOIN com_gastos_venta cgv ON cgv.id_datos_venta = cdv.id
        WHERE cdv.clave_producto = 'us' and cdv.estatus = 4 and id_vendedor = $idVendedor");
    
}

public function comisionesAutorizadasSeguros($idVendedor){
    return DB::connection('autos')->select(
        "SELECT 
            id,
            estatus,
            comision_apv_pesos AS comision_apv,
            CONCAT('Folio: ',folio,' No Póliza: ', poliza,' Fecha: ',fecha_emision) AS descripcion,        
            observaciones,
            prima_neta as importe_venta
        FROM com_seguro
        WHERE estatus =2 AND com_vendedores_id = $idVendedor");
}

public function comisionesAutorizadasFinanciamiento($idVendedor){
    return DB::connection('autos')->select(
        "SELECT 
            id,
            estatus,
            comision_asesor_pesos AS comision_apv,
            CONCAT('Factura: ',numero_factura,' No Contrato: ', no_contrato, ' Fecha: ',fecha_desembolso) AS descripcion,      
            observaciones,
            monto_financiar as importe_venta
        FROM com_financiamiento
        WHERE estatus = 2 AND com_vendedores_id = $idVendedor");
}

public function comisionesAutorizadasTomaUnidad($idVendedor){
    return DB::connection('autos')->select(
        "SELECT 
            id,
            estatus,
            comision_apv_pesos AS comision_apv,
            CONCAT('Vehiculo: ',clave_producto,'-',anio,'-',no_inventario,' Fecha de toma: ',fecha_toma) AS descripcion,       
            observaciones,
            '0' as importe_venta
        FROM com_toma_unidad
        WHERE estatus = 2 AND com_vendedores_id = $idVendedor");
}

public function comisionesAutorizadasAccesorios($idVendedor){
    return DB::connection('autos')->select(
        "SELECT 
            id,
            estatus,
            comision_apv_pesos AS comision_apv,
            no_factura AS descripcion,       
            observaciones,
            sub_total_factura as importe_venta
        FROM com_accesorios
        WHERE estatus = 2 AND com_vendedores_id = $idVendedor");
}

public function setPendienteAutorizacion( $rubro, $idRegistro , $estatus, $comentario){

    $tabla = match ($rubro) {
        'nuevos' => 'com_datos_venta',
        'seminuevos' => 'com_datos_venta',
        'accesorios' => 'com_accesorios',
        'seguros' => 'com_seguro',
        'financimaientos' => 'com_financiamiento',
        'toma_de_unidades' => 'com_toma_unidad',
        default => null
    };

    $camposEditables = match ($rubro) {
        'nuevos' => ['estatus' => $estatus, 'observacion' => $comentario],
        'seminuevos' => ['estatus' => $estatus, 'observacion' => $comentario],
        'accesorios' => ['estatus' => $estatus, 'comentario' => $comentario],
        'seguros' => ['estatus' => $estatus, 'comentario' => $comentario],
        'financimaientos' => ['estatus' => $estatus, 'comentario' => $comentario],
        'toma_de_unidades' => ['estatus' => $estatus, 'comentario' => $comentario],
         default =>  ['estatus' => $estatus]
    };

    if($tabla){
        DB::connection('autos')->table($tabla)->where('id', $idRegistro)->update($camposEditables);
    }
}


}