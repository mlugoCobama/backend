<?php

namespace Modules\Compras\Services;

use App\Models\Sucursales;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Models\DatosTanque;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Macro\Models\SeguroVehiculo;
use Modules\Compras\Integrations\TecnoGpsApi;
use Modules\Compras\Models\ObservacionVehiculo;
use Modules\Compras\Models\Tags;
use Modules\Compras\Models\TarjetasToka;
use Modules\Compras\Models\VehiculosTags;
use Modules\Compras\Models\VehiculosToka;

class ParqueVehicularService{

    protected $gpsApi;
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

    public function __construct(TecnoGpsApi $gpsApi)
    {
        $this->gpsApi = $gpsApi;
    }

        /**
     * Almacena los datos del vehículo y retorna el id
     *
     * @param array $data
     * @param array $intercompania
     * @return $dataVehiculo->id
     */
    public function storeVehiculo($data, $intercompania, $userId)
    {

        $datosVehiculos =  $data;

        $dataVehiculo = new DatosVehiculo();

        $dataVehiculo->marca = $datosVehiculos['marca'];
        $dataVehiculo->id_sucursal = $this->getIdSucursal($intercompania);
        $dataVehiculo->submarca = $datosVehiculos['submarca'];
        $dataVehiculo->modelo = $datosVehiculos['modelo'];
        $dataVehiculo->no_serie = $datosVehiculos['no_serie'];
        $dataVehiculo->placas = $datosVehiculos['placas'];
        $dataVehiculo->nro_economico = $datosVehiculos['nro_economico'];
        $dataVehiculo->id_cre = $datosVehiculos['id_cre'];
        $dataVehiculo->tipo_combustible = $datosVehiculos['tipo_combustible'];
        $dataVehiculo->tipo = $datosVehiculos['tipo_vehiculo'];
        $dataVehiculo->num_tarjeta_toka = $datosVehiculos['num_tarjeta_toka'];
        $dataVehiculo->num_tag = $datosVehiculos['num_tag'];
        $dataVehiculo->limite = $datosVehiculos['limite'];
        $dataVehiculo->estatus = $datosVehiculos['estatus'];
        $dataVehiculo->categoria = $datosVehiculos['categoria'];
        $dataVehiculo->gps = $datosVehiculos['gps'];
        $dataVehiculo->capacidad_combustible = $datosVehiculos['tanque_combustible'];
        $dataVehiculo->rendimiento_x_litro = $datosVehiculos['rendimiento'];
        $dataVehiculo->activo = 2;

        $dataVehiculo->save();

        if (isset($datosVehiculos['observacion']) && !empty($datosVehiculos['observacion'])) {
            $this->saveObservacionVehiculo($datosVehiculos['observacion'], $dataVehiculo->id, $userId);
        }

        return $dataVehiculo->id;
    }

    public function updateVehiculo($data, $id, $userId)
    {
        $vehiculo = DatosVehiculo::find($id);
        if (!$vehiculo) {
            throw new \Exception("Vehiculo no encontrado");
        }

        $vehiculo->update([
            'marca' => $data['marca'],
            'submarca' => $data['submarca'],
            'modelo' => $data['modelo'],
            'no_serie' => $data['no_serie'],
            'placas' => $data['placas'],
            'tipo' => $data['tipo_vehiculo'],
            'nro_economico' => $data['nro_economico'],
            'id_cre' => $data['id_cre'],
            'tipo_combustible' => $data['tipo_combustible'],
            'estatus' => $data['estatus'],
            'categoria' => $data['categoria'],
            'gps' => $data['gps'],
            'num_tarjeta_toka' => $data['num_tarjeta_toka'],
            'num_tag' => $data['num_tag'],
            'limite' => $data['limite'],
            'capacidad_combustible' => $data['tanque_combustible'],
            'rendimiento_x_litro' => $data['rendimiento'],
        ]);

        if (isset($data['observacion']) && !empty($data['observacion'])) {
            $this->saveObservacionVehiculo($data['observacion'], $id, $userId);
        }
    }

        /**
     * Almacena los datos del tanque
     *
     * @param array $id
     * @param array $data
     * @param array $intercompania
     */
    public function storeTanque($id, $data, $intercompania)
    {

        $datosTanque =  $data;

        $dataTanque = new DatosTanque();

        $dataTanque->com_datos_vehiculo_id = $id;
        $dataTanque->marca = $datosTanque['marca_tanque'];
        $dataTanque->id_sucursal = $this->getIdSucursal($intercompania);
        $dataTanque->anio_fabricacion = $datosTanque['anio_fabricacion'];
        $dataTanque->capacidad = $datosTanque['capacidad'];
        $dataTanque->serie = $datosTanque['serie'];
        $dataTanque->tipo_medidor = $datosTanque['tipo_medidor'];

        $dataTanque->save();
    }

    public function updateTanque($data, $id, $numIntercompania, $id_vehiculo)
    {

        $tanque = DatosTanque::find($id);

        if (!$tanque || empty($id)) {
            $this->storeTanque($id_vehiculo, $data, $numIntercompania);
        } else {
            $tanque->update([
                'marca' => $data['marca_tanque'],
                'anio_fabricacion' => $data['anio_fabricacion'],
                'capacidad' => $data['capacidad'],
                'serie' => $data['serie'],
                'tipo_medidor' => $data['tipo_medidor']
            ]);
        }
    }

    public function updateDatosPoliza($data, $id_vehiculo, $idPoliza)
    {
        $poliza = SeguroVehiculo::find($idPoliza);


        if (!$poliza || empty($poliza)) {
            $this->storeDatosPoliza($data, $id_vehiculo);
        } else {
            $poliza->update([

                'aseguradora' => $data['aseguradora'],
                'inciso_vehiculo' => $data['inciso_vehiculo'],
                'cobertura' => $data['cobertura'],
                'inicio_vigencia' => $data['inicio_vigencia'],
                'fin_vigencia' => $data['fin_vigencia'],
                'flotilla' => $data['flotilla'],
                'inciso_foltilla' => $data['inciso_foltilla'],

            ]);
        }
    }

        public function storeDatosPoliza($data, $idVehiculo)
    {
        $datosPoliza =  $data;

        $ultimoSeguro = SeguroVehiculo::where('id_com_datos_vehiculo', $idVehiculo)
                                  ->latest('id')
                                  ->first();

        $nuevo = [
            'aseguradora'       => $datosPoliza['aseguradora'],
            'cobertura'         => $datosPoliza['cobertura'],
            'fecha_renovacion'  => $datosPoliza['fecha_emision'],
            'inicio_vigencia'   => $datosPoliza['inicio_vigencia'],
            'fin_vigencia'      => $datosPoliza['fin_vigencia'],
            'flotilla'          => $datosPoliza['numero_poliza'],
            'inciso_foltilla'   => $datosPoliza['inciso'],
            'ramo'              => $datosPoliza['ramo'],
            'sub_ramo'          => $datosPoliza['subramo'],
            'prima_total'       => $datosPoliza['prima_total'],
            'tipo_movimiento'   => $datosPoliza['tipo_movimiento'],
            'periodicidad_pago' => $datosPoliza['periodicidad_pago'],
        ];


        if ($ultimoSeguro) {
            $existente = $ultimoSeguro->only(array_keys($nuevo));

            $diferencias = array_diff_assoc($nuevo, $existente);

            if (empty($diferencias)) {
                return;
            }
        }

        $dataPoliza = new SeguroVehiculo($nuevo);
        $dataPoliza->id_com_datos_vehiculo = $idVehiculo;
        $dataPoliza->save();
    }

    public function getIdSucursal($intercompania)
    {
        $sucursal = Sucursales::where('num_intercompania', $intercompania)->first();
        return $sucursal->id;
    }

    public function saveObservacionVehiculo($comentario, $id_vehiculo, $userId)
    {
        $dataObservacion = new ObservacionVehiculo();
        $dataObservacion->observaciones = $comentario;
        $dataObservacion->datos_vehiculo_id = $id_vehiculo;
        $dataObservacion->user_id = $userId;
        $dataObservacion->save();
    }

    public function asignarToka( $idToka, $idVehiculo)
    {
        DB::transaction(function () use ($idToka, $idVehiculo) {
            $asignacionActual = VehiculosToka::where('com_id_datos_vehiculos', $idVehiculo)->whereNull('fecha_fin')->first();

            if (is_null($idToka)) {
                if ($asignacionActual) {
                    $asignacionActual->update(['fecha_fin' => now()]);
                    TarjetasToka::where('id', $asignacionActual->com_id_tarjetas_toka)->update(['estatus' => '0']);
                }
                return;
            }
            $tarjetaAsignada = VehiculosToka::where('com_id_tarjetas_toka',$idToka)->whereNull('fecha_fin')->first();

            if ($tarjetaAsignada &&$tarjetaAsignada->com_id_datos_vehiculos != $idVehiculo) {
                throw new \Exception('La tarjeta ya se encuentra asignada a otro vehículo.');
            }
            if ($asignacionActual) {
                if ($asignacionActual->com_id_tarjetas_toka == $idToka) {
                    return;
                }

                $asignacionActual->update(['fecha_fin' => now()]);
                TarjetasToka::where('id',$asignacionActual->com_id_tarjetas_toka)->update(['estatus' => '0']);
            }
            VehiculosToka::create([
                'com_id_datos_vehiculos' => $idVehiculo,
                'com_id_tarjetas_toka' => $idToka,
                'fecha_inicio' => now(),
            ]);
            TarjetasToka::where('id', $idToka)->update(['estatus' => '1']);
        });
    }


    public function asignarTag( $idTag, $idVehiculo)
    {
        DB::transaction(function () use ($idTag, $idVehiculo) {
            $asignacionActual = VehiculosTags::where('com_id_datos_vehiculos', $idVehiculo)->whereNull('fecha_fin')->first();
            if (is_null($idTag)) {
                if ($asignacionActual) {
                    $asignacionActual->update(['fecha_fin' => now()]);
                    Tags::where('id', $asignacionActual->com_id_tags)->update(['estatus' => '0']);
                }
                return;
            }

            $tarjetaAsignada = VehiculosTags::where('com_id_tags',$idTag)->whereNull('fecha_fin')->first();

            if ($tarjetaAsignada &&$tarjetaAsignada->com_id_datos_vehiculos != $idVehiculo) {
                throw new \Exception(
                    'La tarjeta ya se encuentra asignada a otro vehículo.'
                );
            }
            if ($asignacionActual) {
                if ($asignacionActual->com_id_tags == $idTag) {
                    return;
                }

                $asignacionActual->update(['fecha_fin' => now()]);
                Tags::where('id', $asignacionActual->com_id_tags)->update(['estatus' => '0']);
            }
            VehiculosTags::create([
                'com_id_datos_vehiculos' => $idVehiculo,
                'com_id_tags' => $idTag,
                'fecha_inicio' => now(),
            ]);
            Tags::where('id', $idTag)->update(['estatus' => '1']);
        });
    }



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

    // Elimina todos los caracteres que no sean dígitos
    public function limpiarNumeroTarjeta($cadena) {
        $soloNumeros = preg_replace('/\D/', '', $cadena);
        return $soloNumeros;
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

        $noAsignados = [];
        $asignados = []; //
        foreach ($data as $item) {
            if(!empty($item['vin'])){
                $vehiculo = DatosVehiculo::where('no_serie', $item['vin'])->first();
                if($vehiculo){
                    $vehiculo->unit_id_gps =  $item['unit_id'];
                    $vehiculo->save();
                    $asignados[] = $item;
                }else{
                    $noAsignados[] = $item;
                }
            }

        }
        return  [
            'totalGps' => count($data),
            'asignados'   => $asignados,
            'totalAsignados' => count($asignados),
            'no_asignados'=> $noAsignados,
            'total_no_asignados' => count($noAsignados),
        ];
    }

    /**
     * Recupera el recorrido de unidades del dia anterior
     */
    public function calcularRecorridoUnidad($idUnidad){
        $fechaInicio = Carbon::yesterday('America/Mexico_City')->startOfDay()
            ->utc()->format('Y-m-d\TH:i:s\Z');
        $fechaFin = Carbon::yesterday('America/Mexico_City')->endOfDay()
            ->utc()->format('Y-m-d\TH:i:s\Z');
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

            if ($route['type'] === 'route') {
                $resumen['distancia_total_metros'] += $route['distance'] ?? 0;
                $inicio = Carbon::parse($route['start']['time']);
                $fin = Carbon::parse($route['end']['time']);
                $resumen['tiempo_manejando_segundos'] +=
                    $inicio->diffInSeconds($fin);
            }

            if ($route['type'] === 'stop') {
                $resumen['veces_detenido']++;
                $inicio =Carbon::parse($route['start']['time']);
                $fin = Carbon::parse($route['end']['time']);
                $resumen['tiempo_detenido_segundos'] +=
                    $inicio->diffInSeconds($fin);
            }
        }

        $resumen['distancia_total_km'] = round($resumen['distancia_total_metros'] / 1000,2);
        $resumen['tiempo_manejando'] =gmdate('H:i:s',$resumen['tiempo_manejando_segundos']);
        $resumen['tiempo_detenido'] =gmdate('H:i:s',$resumen['tiempo_detenido_segundos']);

        return $resumen;
    }
}
