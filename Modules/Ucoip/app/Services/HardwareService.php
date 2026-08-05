<?php

namespace Modules\Ucoip\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Ucoip\Models\HardwarePcModel;
use Modules\Ucoip\Models\Resguardo;
use App\Enums\EstatusActivos;
use Modules\Ucoip\Models\CatEmpresas;

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
            $hardware->estado           = $data['estado'] ?? EstatusActivos::DISPONIBLE;
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
            if ($hardware->estado != EstatusActivos::ASIGNADA) {
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
            $hardware->estado = EstatusActivos::ASIGNADA; // asignado
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

    public function generarNoInventario($idEmpresa)
    {
        $empresa = CatEmpresas::findOrFail($idEmpresa);
        $ultimoRegistro = HardwarePcModel::where('cat_empresa_id', $idEmpresa)
            ->orderByDesc('id')
            ->first();
        if (!$ultimoRegistro) {
            $consecutivo = 1;
        } else {
            $ultimoConsecutivo = intval(substr($ultimoRegistro->no_inventario, -6));
            $consecutivo = $ultimoConsecutivo + 1;
        }
        return $empresa->intercompania . '-' . str_pad($consecutivo, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generates a new hardware record or returns an existing one based on the provided details.
     *
     * @param string $marca The brand of the hardware (required).
     * @param string $modelo The model of the hardware (optional, defaults to 'N/D').
     * @param string $no_serie The serial number of the hardware (required).
     * @param string $tipo_cpu The CPU type of the hardware (optional).
     * @param string $mac The MAC address of the hardware (optional).
     * @param string $memoria_ram The RAM size of the hardware (optional).
     * @param string $disco_duro The disk space of the hardware (optional).
     * @param string $procesador The processor details of the hardware (optional).
     * @param string $caracteristicas Additional characteristics of the hardware (optional).
     * @param string $observaciones Observations or notes about the hardware (optional).
     * @param string $estado The status of the hardware (optional, defaults to 'DISPONIBLE').
     * @param int $empresa The ID of the company associated with the hardware (required).
     * @param int $cat_hardware_id The category ID of the hardware (optional, defaults to 1).
     * @param int $estado_fisico The physical status of the hardware (optional, defaults to 2).
     *
     */
    public function generateHardware(
        $marca, $modelo, $no_serie,
        $tipo_cpu, $mac, $memoria_ram,
        $disco_duro, $procesador, $caracteristicas,
        $observaciones, $estado, $empresa,
        $cat_hardware_id, $estado_fisico)
    {


        // Catalogo de series  genericos o que pueden generar redundancia
        $seriesInvalidas = ['N/D', 'N/A', '0000000', 'SIN SERIE', 'PENDIENTE','', 'N/H'];
        $marcasInvalidas = ['N/D', 'N/A', 'GENERICO', 'SIN MARCA', 'DESCONOCIDO','', 'N/H'];

        $marcaRecibida = trim(mb_strtoupper($marca ?? ''));
        $serieRecibida = trim(mb_strtoupper($no_serie ?? ''));

        $seriesInvalidasUpper = array_map('mb_strtoupper', $seriesInvalidas);
        $marcasInvalidasUpper = array_map('mb_strtoupper', $marcasInvalidas);

        $marcaEsValida = !empty($marcaRecibida) && !in_array($marcaRecibida, $marcasInvalidasUpper);
        $serieEsValida = !empty($serieRecibida) && !in_array($serieRecibida, $seriesInvalidasUpper);

        //Si ninguno de los dos es valido se retorna un false
        if (!$marcaEsValida && !$serieEsValida) {
            return false;
        }

        // Validamos que no exista mas hardware con ese mismo numero de serie
        if (!empty($no_serie) && $serieEsValida) {
            $existe = HardwarePcModel::where('no_serie', $no_serie)->first();
            if ($existe) {
                return $existe; // Retorna el registro existente si ya fue creado
            }
        }

        // Si no existe, se crea el nuevo registro con los campos actualizados
        $hardware = HardwarePcModel::create([
            "no_inventario"    => $this->generarNoInventario($empresa),
            "marca"            => $marca ?? 'N/D',
            "modelo"           => $modelo ?? 'N/D',
            "no_serie"         => $no_serie ?? 'N/D',
            "tipo"             => $tipo_cpu ?? null, // Mapeado desde tipo_cpu
            "mac"              => $mac ?? 'N/D',
            "memoria_ram"      => $memoria_ram ?? 'N/D',
            "disco_duro"       => $disco_duro ?? 'N/D',
            "procesador"       => $procesador ?? 'N/D',
            "caracteristicas"  => $caracteristicas ?? '',
            "observaciones"    => $observaciones ?? '',
            "estado"           => $estado ?? EstatusActivos::DISPONIBLE,
            "cat_empresa_id"   => $empresa,
            "cat_hardware_id"  => $cat_hardware_id ?? 1,
            "estado_fisico"    => $estado_fisico ?? 2
        ]);

        return $hardware;
    }

}
