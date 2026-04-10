<?php

namespace Modules\Nissan\Services;

use Illuminate\Support\Facades\DB;
use App\Enums\EstatusComisionesAutos;
use App\Enums\EstatusComisionesVentasAutos;
use Modules\Nissan\Models\ComCorte;

class ConcentradoService
{
public function generar($fechaInicio, $fechaFin, $claveCorte, $agencia, $comisiones)
{
    return DB::transaction(function () use ($fechaInicio, $fechaFin, $claveCorte, $agencia, $comisiones) {

        // 1. Crear corte

        $corte = new ComCorte();
        $corte->fecha_corte = now();
        $corte->fecha_inicio = $fechaInicio;
        $corte->fecha_fin =    $fechaFin;
        $corte->clave_corte =  $claveCorte;
        $corte->agencia =  $agencia;
        $corte->created_at =   now();
        $corte->save();
 
        $corteId = $corte->id;

        // $corteId = DB::connection('autos')->table('com_corte')->insertGetId([
        //     'fecha_corte'  => now(),
        //     'fecha_inicio' => $fechaInicio,
        //     'fecha_fin'    => $fechaFin,
        //     'clave_corte'  => $claveCorte,
        //     'agencia'  => $agencia,
        //     'created_at'   => now()
        // ]);

        if (empty($comisiones)) {
            throw new \Exception("No hay comisiones seleccionadas");
        }

        foreach ($comisiones as $vendedor) {

            $vendedorCorteId = DB::connection('autos')->table('com_vendedores_corte')->insertGetId([
                'com_corte_id'          => $corteId,
                'com_vendedores_id'     => $vendedor['id'],

                'total_nuevos'          => $vendedor['nuevos']          ?? 0,
                'total_seminuevos'      => $vendedor['seminuevos']      ?? 0,
                'total_financiamiento'  => $vendedor['financiamiento']  ?? 0,
                'total_seguros'         => $vendedor['seguros']         ?? 0,
                'total_accesorios'      => $vendedor['accesorios']      ?? 0,
                'total_toma_unidad'     => $vendedor['toma_unidades']   ?? 0,
                'total_otros'           => $vendedor['otros']           ?? 0,
                'total_comisiones'      => $vendedor['total']           ?? 0,

                'com_factura'           => $vendedor['comision_factura']   ?? 0,
                'desc_nomina'           => $vendedor['desc_nomina']        ?? 0,
                'desc_pretaciones'      => $vendedor['prestaciones']       ?? 0,
                'des_otros'             => $vendedor['otros_descuentos']   ?? 0,
                'desc_c_casa'           => $vendedor['desc_c_casa']        ?? 0,
                'desc_infonavit'        => $vendedor['infonavit']          ?? 0,
                'nomina'                => $vendedor['nomina']             ?? 0,
                'monto_dispersar'       => $vendedor['monto_dispersar']    ?? 0,
                'observaciones'         => $vendedor['observaciones']      ?? '',

                'created_at' => now()
            ]);

            $this->marcarPartidas($vendedor['id'], $corteId, $agencia);
        }

        return $corteId;
    });
}

private function marcarPartidas($vendedorId, $corteId, $agencia): void
{
    $autorizado = EstatusComisionesAutos::AUTORIZADA;
    $pagado     = EstatusComisionesAutos::PAGADA; 

    $autorizadoAutos = EstatusComisionesVentasAutos::REV_RH;
    $pagadoAutos = EstatusComisionesVentasAutos::PAGADO;

    $tablas = [
        ['tabla' => 'com_financiamiento', 'campo_vendedor' => 'com_vendedores_id'],
        ['tabla' => 'com_seguro',         'campo_vendedor' => 'com_vendedores_id'],
        ['tabla' => 'com_accesorios',     'campo_vendedor' => 'com_vendedores_id'],
        ['tabla' => 'com_toma_unidad',    'campo_vendedor' => 'com_vendedores_id'],
    ];

    foreach ($tablas as $origen) {
        DB::connection('autos')
            ->table($origen['tabla'])
            ->where($origen['campo_vendedor'], $vendedorId)
            ->where('estatus', $autorizado)
            ->where('agencia', $agencia)
            ->update([
                'estatus'      => $pagado,
                'com_corte_id' => $corteId,
                'updated_at'   => now()
            ]);
    }

    $idsNuevosSeminuevos = DB::connection('autos')
        ->table('com_datos_venta as cdv')
        ->join('com_gastos_venta as cgv', 'cgv.id_datos_venta', '=', 'cdv.id')
        ->where('cdv.id_vendedor', $vendedorId)
        ->whereIn('cdv.clave_producto', ['nu', 'us'])
        ->where('cdv.estatus', $autorizadoAutos)
        ->where('cdv.agencia', $agencia)
        ->pluck('cdv.id');

    if ($idsNuevosSeminuevos->isNotEmpty()) {
        DB::connection('autos')
            ->table('com_datos_venta')
            ->whereIn('id', $idsNuevosSeminuevos)
            ->update([
                'estatus'      => $pagadoAutos,
                'pagado'       => 1,
                'com_corte_id' => $corteId,
                'updated_at'   => now()
            ]);
    }
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
            'nuevos' => EstatusComisionesVentasAutos::PAGADO,
            'seminuevos' => EstatusComisionesVentasAutos::PAGADO,
            'financiamiento' => EstatusComisionesAutos::PAGADA,
            'seguros' => EstatusComisionesAutos::PAGADA,
            'accesorios' => EstatusComisionesAutos::PAGADA,
            'toma_unidad' => EstatusComisionesAutos::PAGADA,
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

    public function comisionesAutorizadasNuevos($idVendedor, $estatus )
    {
        return DB::connection('autos')->select(
            "SELECT 
        cdv.id,
        cdv.estatus,
        cgv.comision_apv_pesos AS comision_apv,
        'Sin observaciones' AS observaciones,
        CONCAT(cdv.no_factura, '-', cdv.descripcion,'-',cdv.clave_producto,'-',cdv.no_inventario ) AS descripcion,
        (cdv.utilidad_inicial - (cgv.otros + cgv.gasolina + cgv.previa + cgv.descuentos + cgv.traslados + cgv.descuento_impulso + cgv.total_subsidios + cgv.descuento_gastos + cgv.cortesia + cgv.accesorios + cgv.placas)) AS importe_venta       
        FROM com_datos_venta cdv 
        INNER JOIN com_gastos_venta cgv ON cgv.id_datos_venta = cdv.id
        WHERE cdv.clave_producto = 'nu' and cdv.estatus = $estatus and id_vendedor = $idVendedor"
        );
    }

    public function comisionesAutorizadasSeminuevos($idVendedor, $estatus)
    {
        return DB::connection('autos')->select(
            "SELECT 
        cdv.id,
        cdv.estatus,
        cgv.comision_apv_pesos AS comision_apv,
        'Sin observaciones' AS observaciones,
        CONCAT(cdv.no_factura, '-', cdv.descripcion,'-',cdv.clave_producto,'-',cdv.no_inventario ) AS descripcion,
        (cdv.utilidad_inicial - (cgv.otros + cgv.gasolina + cgv.previa + cgv.descuentos + cgv.traslados + cgv.descuento_impulso + cgv.total_subsidios + cgv.descuento_gastos + cgv.cortesia + cgv.accesorios + cgv.placas)) AS importe_venta       
        FROM com_datos_venta cdv 
        INNER JOIN com_gastos_venta cgv ON cgv.id_datos_venta = cdv.id
        WHERE cdv.clave_producto = 'us' and cdv.estatus = $estatus and id_vendedor = $idVendedor"
        );
    }

    public function comisionesAutorizadasSeguros($idVendedor, $estatus )
    {
        return DB::connection('autos')->select(
            "SELECT 
            id,
            estatus,
            comision_apv_pesos AS comision_apv,
            CONCAT('Folio: ',folio,' No Póliza: ', poliza,' Fecha: ',fecha_emision) AS descripcion,        
            observaciones,
            prima_neta as importe_venta
        FROM com_seguro
        WHERE estatus =$estatus AND com_vendedores_id = $idVendedor"
        );
    }

    public function comisionesAutorizadasFinanciamiento($idVendedor, $estatus)
    {
        return DB::connection('autos')->select(
            "SELECT 
            id,
            estatus,
            comision_asesor_pesos AS comision_apv,
            CONCAT('Factura: ',numero_factura,' No Contrato: ', no_contrato, ' Fecha: ',fecha_desembolso) AS descripcion,      
            observaciones,
            monto_financiar as importe_venta
        FROM com_financiamiento
        WHERE estatus = $estatus AND com_vendedores_id = $idVendedor"
        );
    }

    public function comisionesAutorizadasTomaUnidad($idVendedor, $estatus)
    {
        return DB::connection('autos')->select(
            "SELECT 
            id,
            estatus,
            comision_apv_pesos AS comision_apv,
            CONCAT('Vehiculo: ',vehiculo,'-',no_inventario,'-',no_serie,' Fecha de toma: ',fecha_toma) AS descripcion,       
            observaciones,
            '0' as importe_venta
        FROM com_toma_unidad
        WHERE estatus = $estatus AND com_vendedores_id = $idVendedor"
        );
    }

    public function comisionesAutorizadasAccesorios($idVendedor, $estatus)
    {
        return DB::connection('autos')->select(
            "SELECT 
            id,
            estatus,
            comision_apv_pesos AS comision_apv,
            no_factura AS descripcion,       
            observaciones,
            sub_total_factura as importe_venta
        FROM com_accesorios
        WHERE estatus = $estatus AND com_vendedores_id = $idVendedor"
        );
    }

    public function setPendienteAutorizacion($rubro, $idRegistro, $estatus, $comentario)
    {

        $tabla = match ($rubro) {
            'nuevos' => 'com_datos_venta',
            'seminuevos' => 'com_datos_venta',
            'accesorios' => 'com_accesorios',
            'seguros' => 'com_seguro',
            'financiamiento' => 'com_financiamiento',
            'toma_de_unidades' => 'com_toma_unidad',
            default => null
        };

        $camposEditables = match ($rubro) {
            'nuevos' => ['estatus' => $estatus, 'observacion' => $comentario],
            'seminuevos' => ['estatus' => $estatus, 'observacion' => $comentario],
            'accesorios' => ['estatus' => $estatus, 'comentario' => $comentario],
            'seguros' => ['estatus' => $estatus, 'comentario' => $comentario],
            'financiamiento' => ['estatus' => $estatus, 'comentario' => $comentario],
            'toma_de_unidades' => ['estatus' => $estatus, 'comentario' => $comentario],
            default =>  ['estatus' => $estatus]
        };

        if ($tabla) {
            DB::connection('autos')->table($tabla)->where('id', $idRegistro)->update($camposEditables);
        }
    }

    
}
