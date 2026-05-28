<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Compras\Models\DatosGps;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Compras\Services\ParqueVehicularService;

class ProcesarGpsUnidades implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pvService;
    /**
     * Create a new job instance.
     */
    public function __construct(ParqueVehicularService $pvService)
    {
        $this->pvService = $pvService;
    }

    /**
     * Execute the job.
     */
    public function handle(): void{
        $unidadesGps = DatosVehiculo::where('unit_id_gps', '>', 1)->get();
        foreach ($unidadesGps as $unidad) {
            $eventos = $this->pvService->calcularRecorridoUnidad($unidad->id);
            $data = $this->pvService->calcularResumenRecorrido($eventos);
            DatosGps::create([
                'com_datos_vehiculos_id' => $unidad->id,
                'distancia_metros' => $data['distancia_total_metros'],
                'distancia_km' => $data['distancia_total_km'],
                'veces_detenido' => $data['veces_detenido'],
                'tiempo_detenido_segundos' => $data['tiempo_detenido_segundos'],
                'tiempo_manejando_segundos' => $data['tiempo_manejando_segundos'],
                'fecha' => Carbon::yesterday()->format('Y-m-d')
            ]);
        }
    }
}
