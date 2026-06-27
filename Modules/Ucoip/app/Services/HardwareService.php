<?php

namespace Modules\Ucoip\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Ucoip\Models\HardwarePcModel;
use Modules\Ucoip\Models\Resguardo;

class HardwareService{

    public function storeHardware($data){
        $existe = false;

        // $existe = HardwarePcModel::where('no_serie', $data['no_serie'])->first();

        if(!$existe){
             $hardware  =  new HardwarePcModel();

            $hardware->marca            = $data['marca'] ?? 'N/D';
            $hardware->modelo           = $data['modelo'] ?? 'N/D';
            $hardware->no_serie         = $data['no_serie'] ?? 'N/D';
            $hardware->tipo             = $data['tipo'] ?? null;
            $hardware->mac              = $data['mac'] ?? 'N/D';
            $hardware->memoria_ram      = $data['memoria_ram'] ?? 'N/D';
            $hardware->disco_duro       = $data['disco_duro'] ?? 'N/D';
            $hardware->procesador       = $data['procesador'] ?? 'N/D';
            $hardware->caracteristicas  = $data['caracteristicas'] ?? '';
            $hardware->observaciones    = $data['observaciones'] ?? '';
            $hardware->estado           = $data['estado'] ?? 1;
            $hardware->cat_hardware_id  = $data['cat_hardware_id'] ?? 1;
            $hardware->cat_empresa_id   = $data['cat_empresa_id'];
            $hardware->save();

            return $hardware;
        }

        return $existe;  
    }

    public function updateEstatusHardware($id, $estado){
        $hardware =  HardwarePcModel::find($id);
        if($hardware){
            $hardware->estado = $estado;
        }
    }


    public function asignarEquipo(int $hardwareId, int $usuarioId){

        DB::beginTransaction();

        try {

            $hardware = HardwarePcModel::findOrFail($hardwareId);

            if ($hardware->estado != 1) {
                throw new \Exception(
                    'El equipo ya no está disponible'
                );
            }

            // Crear resguardo
            $resguardo = Resguardo::create([
                'hardware_id' => $hardware->id,
                'usuario_id' => $usuarioId,
                'fecha' => now()
            ]);

            // actualizar estado
            $hardware->estado = 1; // asignado
            $hardware->save();

            DB::commit();

            return $resguardo;
        } catch (\Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }

    // -----------------------------------------------------------
    // Recuperación de datos tactical
    // -----------------------------------------------------------

    /**
     * Petición de datos a tactical por id de la empresa en tactical
     */

    public function getDevicesEmpresa($id){
        $response = Http::withHeaders([
            'x-api-key' => env('TAC_API_KEY'),
            'Accept' => 'application/json',
            ])->get(env('TAC_API_ROUTE'), [
                'site' => $id
            ]);

        return $response->json();
    }


    // -----------------------------------------------------------
    // Normalización de datos tactical
    // -----------------------------------------------------------

    /**
     * Asigna un tipos de pc 
     * 1 pc de de marca
     * 2 pc armada
     */
    public function validateOrigin($serie){
        return $serie == "To be filled by O.E.M." ? 2 : 1;
    }

    /**
     * Normaliza la capacidad del dispositivo
     */
    public function obtenerCapacidad(string $texto)
    {
        preg_match('/(\d+(?:\.\d+)?(?:GB|TB))/', $texto, $matches);
        return $matches[1] ?? null;
    }


    /**
     * Separa la marca y el modelo de una cadena de texto
     */
    public function separarMarcaModelo(string $texto): array
    {
        $texto = trim($texto);
        $partes = explode(' ', $texto);
        $marca = $partes[0] ?? null;

        $modelo = implode(' ', array_slice($partes, 1));

        $modelo = str_replace([
            'Technology Co., Ltd.',
            'Inc.',
            'Corporation',
            'COMPUTER INC.',
            'Ltd.',
            'Co.,'
        ], '', $modelo);

        $modelo = trim(preg_replace('/\s+/', ' ', $modelo));

        return [
            'marca' => $marca,
            'modelo' => $modelo
        ];
    }


}