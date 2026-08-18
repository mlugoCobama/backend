<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Modules\Volumetricos\Models\ReporteVolumen;

class ActualizarFechasReporte extends Command
{
    /**
     * El nombre y firma del comando de consola.
     * Uso: php artisan reportes:actualizar-fechas
     */
    protected $signature = 'reportes:actualizar-fechas';

    /**
     * Descripción del comando.
     */
    protected $description = 'Lee los archivos JSON almacenados y actualiza la fecha_reporte en la base de datos';

    public function handle()
    {
        // 1. Obtener los reportes que tienen un JSON asociado en 'ruta_archivo'
        $reportes = ReporteVolumen::whereNotNull('ruta_archivo')->get();

        if ($reportes->isEmpty()) {
            $this->warn('No se encontraron reportes con JSON para procesar.');
            return 0;
        }

        $this->info("Iniciando procesamiento de {$reportes->count()} reportes...");
        $bar = $this->output->createProgressBar($reportes->count());
        $bar->start();

        $actualizados = 0;
        $errores = 0;

        foreach ($reportes as $reporte) {
            $rutaJson = $reporte->ruta_archivo;

            if (!Storage::disk('public')->exists($rutaJson)) {
                $this->info("Archivo no encontrado: {$rutaJson} (ID: {$reporte->id})");
                $errores++;
                $bar->advance();
                continue;
            }

            $contenido = Storage::disk('public')->get($rutaJson);
            $json = json_decode($contenido, true);

            if (!$json) {
                $errores++;
                $bar->advance();
                continue;
            }

            $fechaIso = $json['FechaYHoraReporteMes']
                     ?? $json['FechaYHoraReporteDiario']
                     ?? $json['fecha_reporte']
                     ?? null;

            if ($fechaIso) {
                try {
                    // Convertir la fecha ISO a formato 'YYYY-MM-DD'
                    // $fechaFormateada = Carbon::parse($fechaIso)->format('Y-m-d');

                    $reporte->update([
                        'fecha_reporte' => $fechaIso
                    ]);

                    $actualizados++;
                    $this->info("Éxito: Archivo {$rutaJson} procesado correctamente. Fecha asignada: {$fechaIso}");
                } catch (\Exception $e) {
                    $errores++;
                    $this->info("Error procesando {$rutaJson} (ID: {$reporte->id}): " . $e->getMessage());
                }
            } else {
                $this->info("No se encontró ninguna fecha válida dentro del JSON en: {$rutaJson} (ID: {$reporte->id})");
                $errores++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Proceso terminado.");
        $this->info("Registros actualizados: {$actualizados}");

        if ($errores > 0) {
            $this->error("Registros omitidos o con error/archivo no encontrado: {$errores}");
        }

        return 0;
    }
}
