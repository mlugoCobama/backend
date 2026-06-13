<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportarTomaUnidades extends Command
{
    protected $signature = 'toma:importar {clave?} {anio?}';
    protected $description = 'Importa unidades usadas al módulo de toma de unidad parametros Anio y clave (US ó NU)' ;

    public function handle()
    {
        $anio = $this->argument('anio') ??  Carbon::now()->format('Y');
        $clave =  !empty($this->argument('clave')) ? strtoupper($this->argument('clave')) : 'US';

         $conexiones = [
            'renault',
            'nissan_universidad',
            'nissan_campestre',
            'nissan_azcapotzalco',
        ];

        $this->info("Iniciando sincronizacion de toma de unidades");
        Log::info("Iniciando sincronizacion de toma de unidades");

        foreach ($conexiones as $conexion) {
            try {

                $this->info("Procesando conexión: {$conexion}");
                Log::info("Procesando conexión: {$conexion}");

                $this->procesarConexion($conexion, $anio, $clave);

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
            // $this->info("Procesando inventario: {$conexion}");
            // Log::info("Procesando inventario: {$conexion}");

            // $this->procesarConexion($conexion, $anio, $clave);
        }

        $this->info('Importación de tomas completada');
        Log::info("Importación de tomas completada");
    }

    private function procesarConexion($conexion, $anio, $clave)
    {
        $origen  = DB::connection($conexion);
        $destino = DB::connection('autos');

        $unidades = $this->queryUnidades($origen, $anio, $clave);

        if ($unidades->isEmpty()) return;

        DB::connection('autos')->transaction(function () use ($unidades, $destino, $anio, $clave, $conexion) {

            foreach ($unidades as $unidad) {

            $agenciaId = $this->obtenerAgencia($conexion, $unidad);
                
                // Validar duplicado por número de inventario
                $existe = $destino->table('com_toma_unidad')
                    ->where('no_serie', $unidad->vehi_serie)
                    ->where('agencia', $agenciaId)
                    ->where('no_inventario', ($clave.'-'.$anio.'-'.$unidad->vehi_numeroinventario))
                    ->exists();

                if ($existe) continue;

                // Insertar registro
                $destino->table('com_toma_unidad')->insert([
                    // 'com_vendedores_id'   => null, // si después lo quieres relacionar
                    'no_inventario'       => $clave.'-'.$anio.'-'.$unidad->vehi_numeroinventario,
                    'vehiculo'            => $unidad->mode_descripcion . ' ' . $unidad->vehi_anio,
                    'comision_apv_pesos'  => 2000,
                    'fecha_toma'          => $unidad->vehi_fechaasignacion,
                    'id_com_datos_venta'  => null,
                    'observaciones'       => null,
                    'comentario'          => null,
                    'estatus'             => 1,
                    'activo'              => 1,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                    'no_serie'            => $unidad->vehi_serie,
                    'agencia'             => $agenciaId,
                    'tipo_apv'            => '',
                    'com_corte_id'        => null,
                ]);
            }
        });
    }

    private function queryUnidades($connection, $anio, $clave)
    {
        return $connection->table('Vt_InventarioAutos as IA')
            ->join('Vt_Modelos as M', 'M.mode_modeloid', '=', 'IA.vehi_mode_modeloid')
            ->where('IA.vehi_clas_clave', $clave)
            ->where('IA.vehi_anio', $anio)
            ->select(
                'IA.vehi_numeroinventario',
                'IA.vehi_anio',
                'IA.vehi_fechaasignacion',
                'IA.vehi_serie',
                'M.mode_descripcion',
                'IA.vehi_idagencia AS id_agencia'
            )
            ->get();
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
}