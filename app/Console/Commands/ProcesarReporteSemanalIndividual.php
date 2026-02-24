<?php

namespace App\Console\Commands;

use App\Jobs\ProcesarReporteSemanalJob;
use Illuminate\Console\Command;

class ProcesarReporteSemanalIndividual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:procesar-reporte-semanal-individual';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera y envía el reporte semanal para una empresa específica';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $intercompania = $this->argument('intercompania');
        $nombre        = $this->argument('nombre');

        // Despachar el Job para una sola empresa
        ProcesarReporteSemanalJob::dispatch($intercompania, $nombre)
            ->onQueue('reportes');

        $this->info("Job generado correctamente para la empresa {$nombre} ({$intercompania})");
    }


}
