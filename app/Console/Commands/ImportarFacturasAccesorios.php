<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportarFacturasAccesorios extends Command
{
    protected $signature = 'facturas:importar {fechaInicio} {fechaFin}';
    protected $description = 'Importa facturas de accesorios desde múltiples conexiones AAAA-DD-MM';

    public function handle()
    {
        $fechaInicio = $this->argument('fechaInicio');
        $fechaFin    = $this->argument('fechaFin');

        $conexiones = [
            'nissan_universidad' => 710,
        ];

        foreach ($conexiones as $conexion => $agenciaId) {

            $this->info("Procesando conexión: {$conexion}");

            $this->procesarConexion($conexion, $agenciaId, $fechaInicio, $fechaFin);
        }

        $this->info('Importación completada');
    }

    private function procesarConexion($conexion, $agenciaId, $fechaInicio, $fechaFin)
    {
        $origen  = DB::connection($conexion);
        $destino = DB::connection('autos');

        $facturas = $this->queryFacturas($origen, $fechaInicio, $fechaFin);

        if ($facturas->isEmpty()) {
            return;
        }

        DB::connection('autos')->transaction(function () use ($facturas, $destino, $agenciaId) {

            $claves = $facturas->pluck('pedi_empl_clave')->unique();

            $empleadosCache = $destino->table('com_vendedores')
                ->where('agencia', $agenciaId)
                ->whereIn('nro_vendedor_as', $claves)
                ->get()
                ->keyBy('nro_vendedor_as');

            foreach ($facturas as $factura) {

                $claveEmpleado = $factura->pedi_empl_clave;
                $empleado = $empleadosCache->get($claveEmpleado);

                if ($empleado) {

                    $empleadoId = $empleado->id;

                } else {

                    // Crear empleado ligado a la agencia actual
                    $empleadoId = $destino->table('com_vendedores')->insertGetId([
                        'nro_vendedor_as'   => $claveEmpleado,
                        'nombre'          => $factura->empl_nombre.' '.$factura->empl_apellidopaterno.' '.$factura->empl_apellidomaterno,
                        'agencia'      => $agenciaId,
                        'clave' => null,
                        'tipo' => 1,
                        'porcentaje_apv' => 1,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);

                    $empleadosCache->put($claveEmpleado, (object)[
                        'id' => $empleadoId,
                        'agencia' => $agenciaId
                    ]);
                }

                // Evitar duplicados por agencia
                $existe = $destino->table('com_accesorios')
                    ->where('no_pedido', $factura->pedi_numeropedido)
                    ->where('agencia', $agenciaId)
                    ->exists();

                if ($existe) continue;

                //  Insertar factura
                $facturaId = $destino->table('com_accesorios')->insertGetId([
                    'no_pedido'  => $factura->pedi_numeropedido,
                    'no_factura' => $factura->pedi_numerofactura,
                    'fecha_factura'  => $factura->pedi_fechafactura,
                    'com_vendedores_id'    => $empleadoId,
                    'agencia'     => $agenciaId,
                    'sub_total_factura'          => $factura->pedi_importetotal,
                    'iva'            => $factura->pedi_ivafactura,
                    'comision_apv_pesos' => $factura->pedi_importetotal * 0.1,
                    'razon_social'   => $factura->pedi_razonfactura,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                // Detalles
                $detallesInsert = collect($factura->detalles)->map(function ($detalle) use ($facturaId) {
                    return [
                        'com_accesorios_id'    => $facturaId,
                        'producto_clave'=> $detalle->depe_prod_claveproducto,
                        'concepto'   => $detalle->prod_descripcion1,
                        'cantidad'      => $detalle->depe_cantidad,
                        'importe'        => $detalle->depe_precio,
                        'precio_lista'  => $detalle->depe_preciolista,
                        'descuento'     => $detalle->depe_descuento,
                        'fecha_alta'    => $detalle->fechaalta,
                        'created_at'    => now(),
                        'updated_at'    => now(),
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
                'E.empl_apellidomaterno'
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
}