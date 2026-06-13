<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportarFacturasServicios extends Command
{
    protected $signature = 'facturas:importar-servicios {fechaInicio?} {fechaFin?}';
    protected $description = 'Importa facturas de servicios AAAA-DD-MM AAAA-DD-MM';

    public function handle()
    {
        $fechaInicio = $this->argument('fechaInicio') ?? Carbon::yesterday()->toDateString();
        $fechaFin = $this->argument('fechaFin') ?? Carbon::today()->toDateString();

        // $conexiones = [
        //     'nissan_universidad' => 710,
        // ];

        $conexiones = [
            'renault',
            'nissan_universidad',
            'nissan_campestre',
            'nissan_azcapotzalco',
        ];

        $this->info('Iniciando Importacion de servicios');
        Log::info('Iniciando Importacion de servicios');

        foreach ($conexiones as $conexion) {

            try {

                $this->info("Procesando conexión: {$conexion}");
                Log::info("Procesando conexión: {$conexion}");

                $this->procesarConexion($conexion, $fechaInicio, $fechaFin);

                $this->info("Conexión {$conexion} procesada correctamente");
                Log::info("Conexión {$conexion} procesada correctamente");

            } catch (\Throwable $e) {

                $this->error("Error en conexión {$conexion}: " . $e->getMessage());

                Log::error("Error en conexión {$conexion}", [
                    'hora' => now(),
                    'mensaje' => $e->getMessage(),
                    'archivo' => $e->getFile(),
                    'linea' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            // $this->info("Procesando servicios: {$conexion}");
            // Log::info("Procesando servicios: {$conexion}");

            // $this->procesarConexion($conexion, $fechaInicio, $fechaFin);
        }

        $this->info('Importación de servicios completada');
        Log::info('Importación de servicios completada');
    }

    private function procesarConexion($conexion, $fechaInicio, $fechaFin)
    {
        $origen  = DB::connection($conexion);
        $destino = DB::connection('autos');

        $fechas = $this->formatDateToConnection($fechaInicio, $fechaFin, $conexion);
        $facturas = $this->queryFacturasServicios($origen, $fechas['fInicio'], $fechas['fFin']);

        if ($facturas->isEmpty()) return;

        DB::connection('autos')->transaction(function () use ($facturas, $destino, $conexion) {

            $claves = $facturas->pluck('vendedor_clave')->unique();

            $empleadosCache = collect();

            // $empleadosCache = $destino->table('com_vendedores')
            //     ->where('agencia', $agenciaId)
            //     ->whereIn('nro_vendedor_as', $claves)
            //     ->get()
            //     ->keyBy('nro_vendedor_as');

            foreach ($facturas as $factura) {

                $agenciaId = $this->obtenerAgencia($conexion, $factura);

                // $claveEmpleado = $factura->vendedor_clave;
                // $empleado = $empleadosCache->get($claveEmpleado);

                if (!$empleadosCache->has($agenciaId)) {

                    $empleados = $destino->table('com_vendedores')
                        ->where('agencia', $agenciaId)
                        ->whereIn('nro_vendedor_as', $claves)
                        ->get()
                        ->keyBy('nro_vendedor_as');

                    $empleadosCache->put($agenciaId, $empleados);
                }

                $cacheAgencia = $empleadosCache->get($agenciaId);

                $claveEmpleado = $factura->vendedor_clave;

                $empleado = $cacheAgencia->get($claveEmpleado);

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

                    // Actualizar cache
                    $cacheAgencia->put($claveEmpleado, (object)[
                        'id' => $empleadoId
                    ]);

                    $empleadosCache->put($agenciaId, $cacheAgencia);
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
            ->join('empleados as E', 'OS.orse_ases_clave', '=', 'E.empl_clave')
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
                'OS.orse_ases_clave as vendedor_clave',
                'E.empl_agen_idagencia AS id_agencia'
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


    private function obtenerAgencia($conexion, $factura)
    {
        return match ($conexion) {
            'renault' => $factura->id_agencia,
            'nissan_universidad' => 710,
            'nissan_azcapotzalco' => 730,
            'nissan_campestre' => 714,
            
            default => $factura->id_agencia
        };
    }

    public function formatDateToConnection($fechaInicio, $fechaFin, $connection){

        return match ($connection) {
                'nissan_universidad' =>  
                                        ['fInicio' => Carbon::parse($fechaInicio)->format('Y-d-m'),
                                        'fFin' => Carbon::parse($fechaFin)->format('Y-d-m')],
                'renault' => ['fInicio' => Carbon::parse($fechaInicio)->format('Y-d-m'),
                                'fFin' => Carbon::parse($fechaFin)->format('Y-d-m')],
                
                default => ['fInicio' => $fechaInicio, 'fFin' => $fechaFin]
        };
    }
}