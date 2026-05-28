<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Compras\Models\DatosGps;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Compras\Services\ParqueVehicularService;

class SincronizarGpsVehiculos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gps:sincronizar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza recorridos GPS del día anterior';

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
        $this->info('Iniciando sincronización GPS...');

        $unidadesGps = DatosVehiculo::activas()->where('unit_id_gps', '>', 1)->get();

        foreach ($unidadesGps as $unidad) {

            try {

                $this->info("Procesando unidad {$unidad->id}");

                $eventos = $this->pvService
                    ->calcularRecorridoUnidad($unidad->id);

                $data = $this->pvService
                    ->calcularResumenRecorrido($eventos);

                DatosGps::updateOrCreate(
                    [
                        'com_datos_vehiculos_id' => $unidad->id,
                        'fecha' => Carbon::yesterday()->format('Y-m-d')
                    ],
                    [
                        'distancia_metros' => $data['distancia_total_metros'],
                        'distancia_km' => $data['distancia_total_km'],
                        'veces_detenido' => $data['veces_detenido'],
                        'tiempo_detenido_segundos' => $data['tiempo_detenido_segundos'],
                        'tiempo_manejando_segundos' => $data['tiempo_manejando_segundos'],
                    ]
                );

                $this->info("Unidad {$unidad->id} completada");

            } catch (\Exception $e) {

                $this->error(
                    "Error en unidad {$unidad->id}: ".$e->getMessage()
                );

            }
        }

        $this->info('Sincronización terminada');

        return Command::SUCCESS;
    }
}
