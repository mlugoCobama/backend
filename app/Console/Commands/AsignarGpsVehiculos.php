<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Compras\Services\ParqueVehicularService;

class AsignarGpsVehiculos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gps:asignar-vehiculos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $pvService;

    public function __construct(ParqueVehicularService $pvService)
    {
        parent::__construct();

        $this->pvService = $pvService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando asignación de GPS...');
        $data = $this->pvService->sincronizarGpsVehiculos();
        $this->info('Se sincronizaron '. $data['totalAsignados'].' de '. $data['totalGps']. ' quedan '. $data['total_no_asignados'] );
        $this->info('Finalizo asignación de GPS...');
    }
}
