<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportarFacturasServicios extends Command
{
    protected $signature = 'facturas:importar-servicios {fechaInicio} {fechaFin}';
    protected $description = 'Importa facturas de servicios AAAA-DD-MM AAAA-DD-MM';

    public function handle()
    {
        $fechaInicio = $this->argument('fechaInicio');
        $fechaFin    = $this->argument('fechaFin');

        $conexiones = [
            'nissan_universidad' => 710,
        ];

        foreach ($conexiones as $conexion => $agenciaId) {

            $this->info("Procesando servicios: {$conexion}");

            $this->procesarConexion($conexion, $agenciaId, $fechaInicio, $fechaFin);
        }

        $this->info('Importación de servicios completada');
    }

    private function procesarConexion($conexion, $agenciaId, $fechaInicio, $fechaFin)
    {
        $origen  = DB::connection($conexion);
        $destino = DB::connection('autos');

        $facturas = $this->queryFacturasServicios($origen, $fechaInicio, $fechaFin);

        if ($facturas->isEmpty()) return;

        DB::connection('autos')->transaction(function () use ($facturas, $destino, $agenciaId) {

            $claves = $facturas->pluck('vendedor_clave')->unique();

            $empleadosCache = $destino->table('com_vendedores')
                ->where('agencia', $agenciaId)
                ->whereIn('nro_vendedor_as', $claves)
                ->get()
                ->keyBy('nro_vendedor_as');

            foreach ($facturas as $factura) {

                $claveEmpleado = $factura->vendedor_clave;
                $empleado = $empleadosCache->get($claveEmpleado);

                if ($empleado) {
                    $empleadoId = $empleado->id;
                } else {
                    $empleadoId = $destino->table('com_vendedores')->insertGetId([
                        'nro_vendedor_as' => $claveEmpleado,
                        'nombre' => 'SIN NOMBRE',
                        'agencia' => $agenciaId,
                        'tipo' => 1,
                        'porcentaje_apv' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $empleadosCache->put($claveEmpleado, (object)[
                        'id' => $empleadoId
                    ]);
                }

                // Validar duplicado
                $existe = $destino->table('com_accesorios')
                    ->where('no_factura', $factura->fase_folio)
                    ->where('agencia', $agenciaId)
                    ->exists();

                if ($existe) continue;

                // Insertar encabezado (SERVICIO)
                $facturaId = $destino->table('com_accesorios')->insertGetId([
                    'no_pedido'             => $factura->fase_orse_folio, // orden servicio
                    'no_factura'            => $factura->fase_folio,
                    'fecha_factura'         => $factura->fase_fecha,
                    'com_vendedores_id'     => $empleadoId,
                    'agencia'               => $agenciaId,
                    'sub_total_factura'     => $factura->fase_subtotal,
                    'iva'                   => $factura->fase_iva,
                    'comision_apv_pesos'    => $factura->fase_subtotal * 0.1,
                    'razon_social'          => $factura->fase_razonsocial,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);

                // Detalles
                $detallesInsert = collect($factura->detalles)->map(function ($detalle) use ($facturaId) {
                    return [
                        'com_accesorios_id' => $facturaId,
                        'producto_clave'    => $detalle->dere_prod_clave,
                        'concepto'          => $detalle->prod_descripcion1,
                        'cantidad'          => $detalle->dere_cantidad,
                        'importe'           => $detalle->dere_precio,
                        'precio_lista'      => $detalle->dere_preciolista,
                        'descuento'         => 0,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                })->toArray();

                if (!empty($detallesInsert)) {
                    $destino->table('com_detalles_accesorios')->insert($detallesInsert);
                }
            }
        });
    }

    private function queryFacturasServicios($connection, $fechaInicio, $fechaFin)
    {
        $facturas = $connection->table('Se_FacturaServicios as FS')
            ->join('Se_OrdenServicio as OS', 'OS.orse_folio', '=', 'FS.fase_orse_folio')
            ->where('FS.fase_cancelado', 0)
            ->whereBetween('FS.fase_fecha', [$fechaInicio, $fechaFin])
            ->select(
                'FS.fase_orse_folio',
                'FS.fase_folio',
                'FS.fase_fecha',
                'FS.fase_razonsocial',
                'FS.fase_subtotal',
                'FS.fase_iva',
                'FS.fase_total',
                'OS.orse_ases_clave as vendedor_clave'
            )
            ->get();

        if ($facturas->isEmpty()) return collect([]);

        $detalles = $connection->table('Se_RefacXOrden as RXO')
            ->join('Re_Productos as RP', 'RXO.dere_prod_clave', '=', 'RP.prod_clave')
            ->whereIn('RXO.dere_orse_folio', $facturas->pluck('fase_orse_folio'))
            ->select(
                'RXO.dere_orse_folio',
                'RXO.dere_prod_clave',
                'RXO.dere_cantidad',
                'RXO.dere_precio',
                'RXO.dere_preciolista',
                'RP.prod_descripcion1'
            )
            ->get()
            ->groupBy('dere_orse_folio');

        return $facturas->map(function ($factura) use ($detalles) {
            $factura->detalles = $detalles->get($factura->fase_orse_folio, collect([]))->values();
            return $factura;
        });
    }
}