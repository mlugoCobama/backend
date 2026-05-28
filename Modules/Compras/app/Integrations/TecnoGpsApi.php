<?php

namespace Modules\Compras\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TecnoGpsApi{

    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = env('TECNO_GPS_ROUTE');
        $this->apiKey = env('TECNO_GPS_API_KEY');
    }

    /**
     * Recupera datos de las ruta de un vehículo en un periodo de tiempo
     * @param int $unitId id del gps de la unidad en la plataforma
     * @param string $fechaInicio fecha en en formato UTC (Z) 
     * @param string $fechaFin fecha en en formato UTC (Z) 
     */
    public function obtenerRutas( int $unitId, string $fechaInicio, string $fechaFin)
    {
        $response = Http::timeout(15)
            ->retry(3,1000)
            ->get("{$this->baseUrl}route/list.json", [
                'key' => $this->apiKey,
                'unit_id' => $unitId,
                'from' => $fechaInicio,
                'till' => $fechaFin,
                'include' => [
                    'speed',         //  Permite obtener información de velocidades
                    'route_details', //  Eventos de conducción
                    'driver_id',     //  Información de conductor
                    'behaviour_data' //  Eventos de conducción brusca
                ]
            ]);

        if (!$response->successful()) {
            throw new \Exception(
                'Error API: '.$response->body()
            );
        }

        return $response->json();
    }

    /**
     * Recupera el catalogo de vehículos con gps de la empresa
     */
    public function obtenerVehiculosEmpresa()
    {
        $response = Http::timeout(15)
            ->retry(3,1000)
            ->get("{$this->baseUrl}unit/list.json?", [
                'key' => $this->apiKey
            ]);
        if (!$response->successful()) {
            throw new \Exception(
                'Error API: '.$response->body()
            );
        }
        return $response->json();
    }

}