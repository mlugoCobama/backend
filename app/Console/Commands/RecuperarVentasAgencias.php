<?php

namespace App\Console\Commands;

use App\Queries\SqlServer\Comisiones\VentasAutosNuevosQuery;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Nissan\Models\DatosVenta;
use Modules\Nissan\Models\TipoVenta;
use Modules\Nissan\Models\Vendedor;

class RecuperarVentasAgencias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recuperar-ventas-agencias {fechaInicio?} {fechaFin?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recupera datos de venta de agencias de un periodo especifico (especifica inicio y fin en formato YYYY-MM-DD) ';

    protected $query;
    /**
     * Execute the console command.
     */
    public function handle(VentasAutosNuevosQuery $query)
    {
        $this->query = $query;
        $conexiones = ['renault', 'nissan_campestre', 'nissan_azcapotzalco', 'nissan_universidad'];
        $fechaInicio = $this->argument('fechaInicio') ?? Carbon::yesterday()->toDateString();
        $fechaFin    = $this->argument('fechaFin')  ?? Carbon::today()->toDateString();

        foreach ($conexiones as $conexion) {

            try {
                $mensajeInicio = "Iniciando sincronización para: {$conexion} nuevos";
                Log::info($mensajeInicio);
                $this->info($mensajeInicio);

                $ventas = $this->sincronizarVentas($conexion, $fechaInicio, $fechaFin);

                $mensajeFin = "Finalizada sincronización {$conexion}. Registros procesados: " . count($ventas);
                Log::info($mensajeFin);
                $this->info($mensajeFin);

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
        }

        $mensajeExito = "Todas las conexiones fueron sincronizadas correctamente.";
        Log::info($mensajeExito);
        $this->info($mensajeExito);
    }

    private function sincronizarVentas($conexion, $fechaInicio, $fechaFin)
    {
        $libroVentas = $this->query->getVentasNuevos($conexion, $fechaInicio, $fechaFin);

        foreach ($libroVentas as $dato) {
            // $existe = DatosVenta::where('no_factura', $dato->faau_nofactura)->exists();
            $existe = !empty($dato->faau_nofactura) ? DatosVenta::where('no_factura', $dato->faau_nofactura)->first() : true;

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
                    'no_factura'        => $dato->faau_nofactura,
                    'razon_social'      => $dato->faau_razonfactura,
                    'descripcion'       => $dato->mode_descripcion,
                    'no_inventario'     => $dato->vehi_numeroinventario,
                    'id_vendedor'       => $vendedorId,
                    'serie'             => $dato->vehi_serie,
                    'total_venta'       => $dato->Venta,
                    'costos'            => $dato->Costo,
                    'bonificaciones'    => $dato->bonificacion,
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
