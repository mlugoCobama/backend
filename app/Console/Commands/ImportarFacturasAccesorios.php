<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportarFacturasAccesorios extends Command
{
    protected $signature = 'facturas:importar {fechaInicio?} {fechaFin?}';
    protected $description = 'Importa facturas de accesorios desde múltiples conexiones AAAA-DD-MM';

    public function handle()
    {
        $fechaInicio = $this->argument('fechaInicio') ?? Carbon::yesterday()->toDateString();
        $fechaFin = $this->argument('fechaFin') ?? Carbon::today()->toDateString();
        

        $conexiones = [
            'renault',
            'nissan_universidad',
            'nissan_campestre',
            'nissan_azcapotzalco',
        ];

        // $conexiones = [
        //     'nissan_universidad' => 710,
        // ];
        
        

        foreach ($conexiones as $conexion) {

            $this->info("Procesando conexión: {$conexion}");
            
            $this->procesarConexion($conexion, $fechaInicio, $fechaFin);
        }

        $this->info('Importación completada');
    }

    private function procesarConexion($conexion, $fechaInicio, $fechaFin)
    {
        $origen  = DB::connection($conexion);
        $destino = DB::connection('autos');

        $fechas = $this->formatDateToConnection($fechaInicio, $fechaFin, $conexion);

        $facturas = $this->queryFacturas($origen, $fechas['fInicio'], $fechas['fFin']);

        if ($facturas->isEmpty()) {
            return;
        }

        DB::connection('autos')->transaction(function () use ($facturas, $destino, $conexion) {

            $claves = $facturas->pluck('pedi_empl_clave')->unique();

            $empleadosCache = collect();

            foreach ($facturas as $factura) {

                 $agenciaId = $this->obtenerAgencia($conexion, $factura);

                if (!$empleadosCache->has($agenciaId)) {

                    $empleados = $destino->table('com_vendedores')
                        ->where('agencia', $agenciaId)
                        ->whereIn('nro_vendedor_as', $claves)
                        ->get()
                        ->keyBy('nro_vendedor_as');

                    $empleadosCache->put($agenciaId, $empleados);
                }

                $cacheAgencia = $empleadosCache->get($agenciaId);

                $claveEmpleado = $factura->pedi_empl_clave;

                $empleado = $cacheAgencia->get($claveEmpleado);

                if ($empleado) {

                    $empleadoId = $empleado->id;
                } else {

                    $empleadoId = $destino->table('com_vendedores')->insertGetId([
                        'nro_vendedor_as' => $claveEmpleado,
                        'nombre' => trim(
                            $factura->empl_nombre . ' ' .
                                $factura->empl_apellidopaterno . ' ' .
                                $factura->empl_apellidomaterno
                        ),
                        'agencia' => $agenciaId,
                        'clave' => null,
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

                // Evitar duplicados por agencia
                $existe = $destino->table('com_accesorios')
                    ->where('no_pedido', $factura->pedi_numeropedido)
                    ->where('agencia', $agenciaId)
                    ->exists();

                if ($existe) continue;

                //  Insertar factura
                $facturaId = $destino->table('com_accesorios')->insertGetId([
                    'no_pedido'             => $factura->pedi_numeropedido,
                    'no_factura'            => $factura->pedi_numerofactura,
                    'fecha_factura'         => $factura->pedi_fechafactura,
                    'com_vendedores_id'     => $empleadoId,
                    'agencia'               => $agenciaId,
                    'sub_total_factura'     => $factura->pedi_importetotal,
                    'iva'                   => $factura->pedi_ivafactura,
                    'comision_apv_pesos'    => $factura->pedi_importetotal * 0.1,
                    'razon_social'          => $factura->pedi_razonfactura,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);

                // Detalles
                $detallesInsert = collect($factura->detalles)->map(function ($detalle) use ($facturaId) {
                    return [
                        'com_accesorios_id'     => $facturaId,
                        'producto_clave'        => $detalle->depe_prod_claveproducto,
                        'concepto'              => $detalle->prod_descripcion1,
                        'cantidad'              => $detalle->depe_cantidad,
                        'importe'               => $detalle->depe_precio,
                        'precio_lista'          => $detalle->depe_preciolista,
                        'descuento'             => $detalle->depe_descuento,
                        'fecha_alta'            => $detalle->fechaalta,
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                })->toArray();

                if (!empty($detallesInsert)) {
                    $destino->table('com_detalles_accesorios')->insert($detallesInsert);
                }
            }
        });
    }

    private function queryFacturas($connection, $fechaInicio, $fechaFin)
    {
        $facturas = $connection->table('Re_EncabezadoPedido as REP')
            ->join('empleados as E', 'REP.pedi_empl_clave', '=', 'E.empl_clave')
            ->where('REP.pedi_status', 'FA')
            ->whereBetween('REP.pedi_fechafactura', [$fechaInicio, $fechaFin])
            ->select(
                'REP.pedi_numeropedido',
                'REP.pedi_numerofactura',
                'REP.pedi_fechafactura',
                'REP.pedi_empl_clave',
                'REP.pedi_razonfactura',
                'REP.pedi_ivafactura',
                'REP.pedi_importetotal',
                'E.empl_nombre',
                'E.empl_apellidopaterno',
                'E.empl_apellidomaterno',
                'E.empl_agen_idagencia AS id_agencia'
            )
            ->get();

        if ($facturas->isEmpty()) return collect([]);

        $detalles = $connection->table('Re_DetallePedido as RDP')
            ->join('Re_Productos as RP', 'RDP.depe_prod_claveproducto', '=', 'RP.prod_clave')
            ->whereIn('RDP.depe_pedi_numeropedido', $facturas->pluck('pedi_numeropedido'))
            ->get()
            ->groupBy('depe_pedi_numeropedido');

        return $facturas->map(function ($factura) use ($detalles) {
            $factura->detalles = $detalles->get($factura->pedi_numeropedido, collect([]))->values();
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