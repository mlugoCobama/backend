<?php

namespace Modules\Compras\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Models\DatosTanque;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Macro\Models\SeguroVehiculo;
use Modules\Compras\Integrations\TecnoGpsApi;

class ParqueVehicularService{

    protected $gpsApi;

    public function __construct(TecnoGpsApi $gpsApi)
    {
        $this->gpsApi = $gpsApi;
    }

    protected $status = [
        "ACTIVA" => 1,
        "EN TALLER" => 2,
        "VENDIDA" => 3,
        "NO IDENTIFICADA" => 4,
        "FUERA DE CIRCULACION" => 0,
        "DESCOMPUESTA" => 5,
        "FISCALIA" => 6,
        "CORRALON" => 7,
        "CHATARRA" => 8,
        "VENDIDA  COMO CHATARRA" => 9,
        "BAJA" => 10
    ];

public function queryVehiculosForToka($idSucursal)
{
    $id = $this->intercompaniaConverter($idSucursal);
    return DB::select('CALL SP_GetPvRecargaToka(?)',[$id]);
}

public function queryVehiculosForTag($idSucursal)
{
    return DatosVehiculo::where('estatus', 1)
        ->where('nro_economico', '<>', '2000')
        ->whereNotNull('num_tag')
        ->where('id_sucursal', $idSucursal)
        ->where('estatus', 1)
        ->get();
}

public function calcularSaldo($saldo, $saliente, $entrante){
    return ($saldo - $saliente) + $entrante;
}




    public function procesarCSV($ruta)
    {
        $handle = fopen($ruta, 'r');
        $encabezados = fgetcsv($handle);

        while (($fila = fgetcsv($handle)) !== false) {
            $datos = array_combine($encabezados, $fila);



            if (isset($datos['numero_serie_v']) && !empty($datos['numero_serie_v'])) {
                
                $vehiculo = DatosVehiculo::where('no_serie', $datos['numero_serie_v'])->first();

                $gps = null;

                if ($datos['GPS'] === "SI") {
                    $gps = ($datos['STATUS GPS'] === 'OK' || empty($datos['STATUS GPS'])) ? 1 : 2;
                } elseif (in_array($datos['GPS'], ["NO", "SIN GPS"]) || empty($datos['SIN GPS'])) {
                    $gps = 3;
                }

                if ($vehiculo) {
                    // Actualizar datos del vehiculo
                    $vehiculo->update([
                        // "id_cre" => $datos['id_cre'],
                        // "nro_economico " => $datos['no_economico'],
                        // "marca" => $datos['marca_v'],
                        // "submarca" => $datos['submarca_v'],
                        // "modelo" => $datos['modelo_v'],
                        // "no_serie" => $datos['numero_serie_v'],
                        // "placas" => $datos['placas'],
                        // "id_sucursal" => $datos['id_sucursal'],
                        // "tipo" => strtolower($datos['uso_vehiculo']),
                        // "estatus" => $status[$datos['status']] ?? 1,
                        // "propietario" => $datos['propietario'],
                        "gps" => $gps,  

                    ]);
                } 
                // else {
                //     if (!empty($datos['numero_serie_v'])) {
                //         // Crear nuevo vehículo
                //         $auto = DatosVehiculo::create([
                //             "id_cre" => $datos['id_cre'] ?? null,
                //             "nro_economico " => $datos['no_economico'] ?? null,
                //             "marca" => $datos['marca_v'] ?? null,
                //             "submarca" => $datos['submarca_v'] ?? null,
                //             "modelo" => $datos['modelo_v'] ?? null,
                //             "no_serie" => $datos['numero_serie_v'] ?? null,
                //             "placas" => $datos['placas'] ?? null,
                //             "id_sucursal" => $datos['id_sucursal'],
                //             "tipo" => $datos['uso_vehiculo'] ?? null,
                //             "estatus" => $status[$datos['status']] ?? 1,
                //             "propietario" => $datos['propietario'] ?? null
                //         ]);

                //         if ($datos['uso_vehiculo'] == 'autotanque') {
                //             DatosTanque::create([
                //                 'marca' => $datos['marca_t'] ?? 'NA',
                //                 'anio_fabricacion' => $datos['anio_fab'] ?? 'NA',
                //                 'capacidad' => $datos['capacidad'] ?? 'NA',
                //                 'serie' => $datos['numero_serie_t'] ?? 'NA',
                //                 'tipo_medidor' => $datos['tipo_medidor'] ?? 'NA',
                //                 'id_sucursal' => $datos['id_sucursal'],
                //                 'com_datos_vehiculo_id' => $auto->id,
                //             ]);
                //         }
                //     }
                // }
            }
        }

        fclose($handle);
    }

    public function importar($ruta)
    {
        $handle = fopen($ruta, 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $auto = DatosVehiculo::create([
                'nro_economico' => $row[0] ?? 'NA',
                'estatus' => $row[2] ?? 'NA',
                'marca' => $row[3] ?? 'NA',
                'submarca' => $row[4] ?? 'NA',
                'modelo' => $row[5] ?? 'NA',
                'no_serie' => $row[6] ?? 'NA',
                'placas' => $row[7] ?? 'NA',
                'id_sucursal' => $row[13] ?? null,
            ]);

            DatosTanque::create([
                'marca' => $row[8] ?? 'NA',
                'anio_fabricacion' => $row[9] ?? 'NA',
                'capacidad' => $row[10] ?? 'NA',
                'serie' => $row[11] ?? 'NA',
                'tipo_medidor' => $row[12] ?? 'NA',
                'id_sucursal' => $row[13] ?? null,
                'com_datos_vehiculo_id' => $auto->id,
            ]);
        }

        fclose($handle);
    }

    public function importarDesdeCsv($archivo)
    {
        $ruta = $archivo->getRealPath();
        $handle = fopen($ruta, 'r');
        $encabezados = fgetcsv($handle);

        while (($fila = fgetcsv($handle)) !== false) {
            $datos = array_combine($encabezados, $fila);

            $vehiculo = DatosVehiculo::where('no_serie', $datos['SERIE'])->first();

            if ($vehiculo) {
                SeguroVehiculo::create([
                    'id_com_datos_vehiculo' => $vehiculo->id,
                    'aseguradora' => 'Banorte',
                    'inciso_vehiculo' => $datos['INCISOV'] ?? null,
                    'cobertura' => $datos['COBERTURA'] ?? null,
                    'inicio_vigencia' => $datos['VIGENCIAI'] ?? null,
                    'fin_vigencia' => $datos['VIGENCIAF'] ?? null,
                    'inciso_foltilla' => $datos['INCISOF'] ?? null,
                    'flotilla' => $datos['FLOTILLA'] ?? null,
                    'fecha_renovacion' => $datos['RENOVACION'] ?? null,
                    'activo' => 1,
                ]);
            }
        }

        fclose($handle);
    }


    /**
     * convierte el intercompania entrante a uno legible para la base datos 
     */
    public function intercompaniaConverter($intercompania){

        return match ((int) $intercompania) {
                240 => 1,  // Servigas del Valle
                353 => 2,  // Gas Urbano
                133 => 3,  // Gas Multiregional
                155 => 4,  // Gasamex
                210 => 5,  // Reyes Gas
                354 => 6,  // Iztagas y Energia
                251 => 7,  // Flamamex
                131 => 8,  // Azteca Gas
                130 => 9,  // Satelite Gas
                110 => 10, // Garza Gas
                111 => 11, // Garza Sur
                250 => 12, // Gas Flamazul
                135 => 13, // Segas
                190 => 14, // Zugas
                132 => 15, // Gas Premio
                191 => 16, // Baragas
                119 => 22, // Tanques Garza Gas
                333 => 35,  // CAS
                default => $intercompania, // si no coincide
            };

        }


    /**
     * -----------------------------------------------------------------
     * INTEGRACION CON GPS
     * -----------------------------------------------------------------
     */


    /**
     * Sincroniza el parque veicular con sus gps
     */
    public function sincronizarGpsVehiculos()
    {
        $rawData = $this->gpsApi->obtenerVehiculosEmpresa();
        $data = $rawData['data']['units'];

        $noAsignados = []; // aquí guardaremos los que no se pudieron mapear
        foreach ($data as $item) {
            $vehiculo = DatosVehiculo::where('no_serie', $item['vin'])->first();
            if($vehiculo){
                // $vehiculo->unit_id_gps =  $item['unit_id'];
                // $vehiculo->save();
            }else{
                 $noAsignados[] = $item;
            }
        }
        return  [
            'asignados'   => $data,
            'no_asignados'=> $noAsignados
        ];
    }

    /**
     * Recupera el recorrido de unidades del dia anterior
     */
    public function calcularRecorridoUnidad($idUnidad){
        // Inicio del dia anterior
        $fechaInicio = Carbon::yesterday('America/Mexico_City')
            ->startOfDay()
            ->utc()
            ->format('Y-m-d\TH:i:s\Z');
        // Fin del dia anterior
        $fechaFin = Carbon::yesterday('America/Mexico_City')
            ->endOfDay()
            ->utc()
            ->format('Y-m-d\TH:i:s\Z');
        $data = [];
        $unidad = DatosVehiculo::find($idUnidad);
        if($unidad && !empty($unidad->unit_id_gps)){
           $data = $this->gpsApi->obtenerRutas($unidad->unit_id_gps, $fechaInicio, $fechaFin);
        }
        return $data;
    }



    /**
     * Calcula datos como distancia_total_metros, distancia_total_km
     * veces_detenido, tiempo_detenido_segundos, tiempo_manejando_segundos
     */
    public function calcularResumenRecorrido($data)
    {
        $resumen = [
            'distancia_total_metros' => 0,
            'distancia_total_km' => 0,
            'veces_detenido' => 0,
            'tiempo_detenido_segundos' => 0,
            'tiempo_manejando_segundos' => 0,
        ];

        $routes = $data['data']['units'][0]['routes'] ?? [];

        foreach ($routes as $route) {

            // calcular tiempos manejando o recorrido
            if ($route['type'] === 'route') {
                $resumen['distancia_total_metros'] += $route['distance'] ?? 0;
                $inicio = Carbon::parse($route['start']['time']);
                $fin = Carbon::parse($route['end']['time']);
                $resumen['tiempo_manejando_segundos'] +=
                    $inicio->diffInSeconds($fin);
            }

            // Cuando está detenido
            if ($route['type'] === 'stop') {
                $resumen['veces_detenido']++;
                $inicio =Carbon::parse($route['start']['time']);
                $fin = Carbon::parse($route['end']['time']);
                $resumen['tiempo_detenido_segundos'] +=
                    $inicio->diffInSeconds($fin);
            }
        }

        // Convertir metros a km
        $resumen['distancia_total_km'] = round($resumen['distancia_total_metros'] / 1000,2);

        // Formatear tiempos
        $resumen['tiempo_manejando'] =gmdate('H:i:s',$resumen['tiempo_manejando_segundos']);

        $resumen['tiempo_detenido'] =gmdate('H:i:s',$resumen['tiempo_detenido_segundos']);

        return $resumen;
    }
}
