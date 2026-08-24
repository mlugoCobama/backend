<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Renault\Services\CitaServicioService;

class RecuperarCitasServicioRenault extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recuperar-citas-servicio-renault';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recupera los datos de citas de servicios';

    /**
     * Execute the console command.
     */
    public function handle(CitaServicioService $citasService)
    {
       $citasService->obtenerOProcesarCitas();

        $this->info('La función se ejecutó correctamente.');

    }
}
