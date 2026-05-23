<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Nissan\Models\DatosVenta;
use Modules\Nissan\Models\TipoVenta;
use Modules\Nissan\Models\Vendedor;

class RecuperarVentasSeminuevosAgencias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recuperar-ventas-seminuevos-agencias {fechaInicio?} {fechaFin?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
   {
        $conexiones = ['renault', 'nissan_campestre', 'nissan_azcapotzalco', 'nissan_universidad'];
        $fechaInicio = $this->argument('fechaInicio') ?? Carbon::yesterday()->toDateString();
        $fechaFin    = $this->argument('fechaFin')  ?? Carbon::today()->toDateString();

        foreach ($conexiones as $conexion) {
            $mensajeInicio = "Iniciando sincronización para: {$conexion}";
            Log::info($mensajeInicio);
            $this->info($mensajeInicio);

            $ventas = $this->sincronizarVentas($conexion, $fechaInicio, $fechaFin);

            $mensajeFin = "Finalizada sincronización {$conexion}. Registros procesados: " . count($ventas);
            Log::info($mensajeFin);
            $this->info($mensajeFin);
        }

        $mensajeExito = "Todas las conexiones fueron sincronizadas correctamente.";
        Log::info($mensajeExito);
        $this->info($mensajeExito);
    }

    private function sincronizarVentas($conexion, $fechaInicio, $fechaFin)
    {
        $libroVentas = DB::connection($conexion)->select("
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

        foreach ($libroVentas as $dato) {
            $existe = DatosVenta::where('no_factura', $dato->faau_nofactura)->exists();

            $agenciaId = match ($conexion) {
                            'renault'               => $dato->id_agencia,   // usar valor que viene de la base 
                            'nissan_universidad'    => 710,                 // intercompania
                            'nissan_campestre'      => 714,                 // intercompania
                            'nissan_azcapotzalco'   => 730,                 // intercompania
                            default                 => $dato->id_agencia,   // usar valor que viene de la base
                        };

            $vendedorId = null;
            $vendedorExiste = Vendedor::where('nro_vendedor_as', $dato->faau_vend_clave)
                                      ->where('agencia', $agenciaId)
                                      ->first();

            if (!$vendedorExiste) {
                $vendedor = Vendedor::create([
                    'tipo' => '1',
                    'porcentaje' => '1',
                    'nro_vendedor_as' => $dato->faau_vend_clave,
                    'agencia' => $agenciaId,
                ]);
                $vendedorId = $vendedor->id;    
            } else {
                $vendedorId = $vendedorExiste->id; 
            }      

            $tipoVenta = TipoVenta::where('nombre', $dato->faau_form_TipoVenta)->first();
            if (!$existe && !empty($vendedorId)) {
                DatosVenta::create([
                    'fecha_as_salida'   => $dato->fecha_salida,
                    'fecha_factura'     => $dato->fecha_factura,
                    'no_factura'        => $dato->faau_nofactura ?? 'TESTND'.$dato->vehi_numeroinventario,
                    'razon_social'      => $dato->faau_razonfactura,
                    'descripcion'       => $dato->mode_descripcion,
                    'no_inventario'     => $dato->vehi_numeroinventario,
                    'id_vendedor'       => $vendedorId,
                    'serie'             => $dato->vehi_serie,
                    'total_venta'       => $dato->Venta,
                    'costos'            => $dato->Costo,
                    'bonificaciones'    => $dato->bonificacion ?? 0,
                    'utilidad_inicial'  => $dato->Utilidad,
                    'tipo_venta_id'     => $tipoVenta->id ?? 2,
                    'tipo_venta'        => $dato->faau_form_TipoVenta,   
                    'clave_producto'    => $dato->vehi_clas_clave,
                    'modelo_producto'   => $dato->mode_clave,
                    'anio_vehiculo'     => $dato->vehi_anio ?? null,
                    'agencia'           => $agenciaId,
                    // 'venta_id'          => $agenciaId,
                ]);
            }
        }
        return $libroVentas;
    }
}
