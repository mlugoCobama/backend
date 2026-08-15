<?php

namespace Modules\Ucoip\Services;

use Illuminate\Support\Facades\DB;
use Modules\Ucoip\Models\CatEmpresas;
use Modules\Ucoip\Models\HardwarePcModel;

class ImportacionHardwareService
{
    public function __construct(
        private CsvReaderService $csvService,
        private UcoipService $ucoipService,
        private HardwareService $hardwareService,
        private ResguardosService $resguardoService,
        private RecursosRedService $recursoRedService,
        private SoftwareService $softwareService
    ) {}

    public function importar($archivo, int $division): void
    {
        $rows = $this->csvService->readCsv($archivo);

        DB::transaction(function () use ($rows, $division) {

            foreach ($rows as $row) {

                $this->procesarFila($row, $division);
                    // $this->updateEmpresa($row, $division);
            }

        });
    }

    private function updateEmpresa(array $row, $division){
        if (
            empty($row['Puesto']) ||
            empty($row['Agencia'])
        ) {
            return;
        }

        $idEmpresa = $this->findEmpresa($row['Agencia'], $division );

        $harwarePc = $this->updateHardware($row['Marca_CPU'], $row['No_Serie_Cpu'], $idEmpresa);
        $harwareMonitor = $this->updateHardware($row['Marca_Monitor'], $row['Serial_Monitor'], $idEmpresa);
        $harwareTeclado = $this->updateHardware($row['Marca_Teclado'], $row['Serial_Teclado'], $idEmpresa);
        $harwareMouse = $this->updateHardware($row['Marca_Mouse'],  $row['Serial_Mouse'], $idEmpresa);
        $harwareUps = $this->updateHardware($row['Marca_UPS'], $row['Serial_UPS'], $idEmpresa);
        $harwareTelefono = $this->updateHardware($row['Marca_Telefono'], $row['Serial_Telefono'], $idEmpresa);
        $impresora = $this->updateHardware($row['Marca_Impresora'], $row['Serie_Impresora'], $idEmpresa);
    }

    public function updateHardware($marca, $no_serie, $idEmpresa)
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
            $existe = HardwarePcModel::where('no_serie', $no_serie)
            // ->where('cat_empresa_id', 10)
            // ->where('marca', $marca)
            ->first();
            if ($existe) {
                $existe->cat_empresa_id = $idEmpresa;
                $existe->save();
            }
        }

    }



    private function findEmpresa($nombreAgencia, $division){
        $empresa = CatEmpresas::where('nombre', 'like', '%'.$nombreAgencia.'%')->where('division',$division)->latest()->first();

        if($empresa){
            return $empresa->id;
        }

        return false;
    }

    private function procesarFila(array $row, int $division): void
    {
        if (
            empty($row['Puesto']) ||
            empty($row['Agencia'])
        ) {
            return;
        }

        $strUcoip = explode('@', $row['Puesto'])[0];
        // $ucoipReal = $row['Puesto'];
        $ucoip = $this->ucoipService->findUcoip(
            $strUcoip,
            // $row['Puesto'],
            $row['Agencia'],
            $division
        );

        $extraObs = '';

        if(!$ucoip){
            $extraObs = ' Referencia: '. $row['Puesto'];
        }

        $tipoCpu = match ($row['Tipo_CPU']) {
            'LAPTOP' => 3,
            'ALL IN ONE' => 5,
            'COMPU DE MARCA'=> 1,
            'COMPU  ARMADA' => 2,
            'TERMINAL' => 4,
            'LAPTOP CONSULT' => 6,
            default => 1
        };


        $harwarePc = $this->hardwareService->generateHardware($row['Marca_CPU'], $row['Modelo_CPU'], $row['No_Serie_Cpu'], $tipoCpu, null, $row['Ram'], $row['Disco Duro'], $row['Procesador'], null, $row['Comentarios_Cpu'].' '.$extraObs, null, $ucoip->cat_empresa_id ?? 15, 1, 2);
        $harwareMonitor = $this->hardwareService->generateHardware($row['Marca_Monitor'], $row['Modelo_Monitor'], $row['Serial_Monitor'], null, null, null, null, null, null, $row['Observaciones_Monitor'].' '.$extraObs, null, $ucoip->cat_empresa_id ?? 15, 2, 2);
        $harwareTeclado = $this->hardwareService->generateHardware($row['Marca_Teclado'], $row['Modelo_Teclado'], $row['Serial_Teclado'], null, null, null, null, null, null, $extraObs, null, $ucoip->cat_empresa_id ?? 15, 3, 2);
        $harwareMouse = $this->hardwareService->generateHardware($row['Marca_Mouse'], $row['Modelo_Mouse'], $row['Serial_Mouse'], null, null, null, null, null, null, $row['Observaciones_Mouse'].' '.$extraObs, null, $ucoip->cat_empresa_id ?? 15, 4, 2);
        $harwareUps = $this->hardwareService->generateHardware($row['Marca_UPS'], $row['Modelo_UPS'], $row['Serial_UPS'], null, null, null, null, null, null, $row['Obsevaciones_UPS'].' '.$extraObs, null, $ucoip->cat_empresa_id ?? 15, 4, 2);
        $harwareTelefono = $this->hardwareService->generateHardware($row['Marca_Telefono'], $row['Modelo_Telefono'], $row['Serial_Telefono'], null, null, null, null, null, null, null.' '.$extraObs, null, $ucoip->cat_empresa_id ?? 15, 7, 2);
        $impresora = $this->hardwareService->generateHardware($row['Marca_Impresora'], $row['Modelo_Impresora'], $row['Serie_Impresora'], null, null, null, null, null, null, $extraObs , null, $ucoip->cat_empresa_id ?? 15, 9, 2);

        $office = false;

        if (!empty($row['Office'])) {
            $office = $this->softwareService->storeSoftware($ucoip->cat_empresa_id ?? 15, $row['Office'], $row['Serial_office'],$extraObs, 2, 2, null, null,null,null);
        }

        $windows = false;

        if (!empty($row['Windows'])) {
           $windows = $this->softwareService->storeSoftware($ucoip->cat_empresa_id ?? 15, $row['Windows'], $row['Serial_windows'],$extraObs, 1, 2, null, null,null, null);
        }

        //! if (!$ucoip) {
        //!     return;
        //! }

        if($ucoip){
            // Asignación de Recursos de Hardware
            if ($harwarePc) {
                $this->resguardoService->asignarRecurso($harwarePc->id, $ucoip->id, $ucoip->user_id);
            }
            if ($harwareMonitor) {
                $this->resguardoService->asignarRecurso($harwareMonitor->id, $ucoip->id, $ucoip->user_id);
            }
            if ($harwareTeclado) {
                $this->resguardoService->asignarRecurso($harwareTeclado->id, $ucoip->id, $ucoip->user_id);
            }
            if ($harwareMouse) {
                $this->resguardoService->asignarRecurso($harwareMouse->id, $ucoip->id, $ucoip->user_id);
            }
            if ($harwareUps) {
                $this->resguardoService->asignarRecurso($harwareUps->id, $ucoip->id, $ucoip->user_id);
            }
            if ($harwareTelefono) {
                $this->resguardoService->asignarRecurso($harwareTelefono->id, $ucoip->id, $ucoip->user_id);
            }
            if ($impresora) {
                $this->resguardoService->asignarRecurso($impresora->id, $ucoip->id, $ucoip->user_id);
            }

            // Asignación de Recursos de Red
            if (!empty($row['Ext_Telefono']) && $harwareTelefono && $row['Ext_Telefono'] != 'N/A') {
                $this->recursoRedService->asignarRecursoRed($harwareTelefono->id, $row['Ext_Telefono'], null, null, $ucoip->id, 4);
            }

            if (!empty($row['IP_Telefono']) && $harwareTelefono ) {
                $this->recursoRedService->asignarRecursoRed($harwareTelefono->id, $row['IP_Telefono'], null, null, $ucoip->id, 3);
            }

            // Procesamiento de Software (Office y Windows)
            if ($office) {
                $this->softwareService->asignacionSoftware($ucoip->id, $office->id, now());
            }

            if ($windows) {
                $this->softwareService->asignacionSoftware($ucoip->id, $windows->id, now());
            }

        }

    }
}
