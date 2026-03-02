<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Renault\Http\Controllers\VisorCitasController;

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
    public function handle()
    {
        $controlador =  new VisorCitasController();
        $controlador->index();

        $this->info('La función se ejecutó correctamente.');

    }
}
