<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Ucoip\Services\HardwareService;
use Modules\Ucoip\Services\ResguardosService;
use Modules\Ucoip\Models\CatEmpresas as ModelsCatEmpresas;


class importarEquiposTactical extends Command
{
    /**
     * Descripción del comando.
     */
    protected $signature = 'facturas:importar {empresa_id}';
    protected $description = 'Importa inventario desde GLPI';

    protected $hwService;
    protected $resguardoService;

    public function __construct(
        HardwareService $hwService,
        ResguardosService $resguardoService
    ) {
        parent::__construct();

        $this->hwService = $hwService;
        $this->resguardoService = $resguardoService;
    }

    public function handle()
    {
        $id = $this->argument('empresa_id');

        $data = $this->hwService->getDevicesEmpresa($id);

        $formatData = [];

        foreach ($data as $item) {

            $equipo = $this->hwService->separarMarcaModelo(
                $item['make_model']
            );

            $ucoip = $this->findUcoipGlpi(
                $item['logged_username'],
                $item['site_name']
            );

            $formatData[] = [
                'marca' => $equipo['marca'],
                'modelo' => $equipo['modelo'],
                'no_serie' => $item['serial_number'],
                'tipo' => $this->hwService->validateOrigin(
                    $item['serial_number']
                ),
                'disco_duro' => $this->hwService->obtenerCapacidad(
                    $item['physical_disks'][0] ?? '0GB'
                ),
                'procesador' => $item['cpu_model'][0] ?? null,
                'cat_hardware_id' => 1,
                'cat_empresa_id' => $ucoip['id_empresa'],
                'nombre_equipo' => $item['hostname'],
                'empresa' => $item['site_name'],
                'usuario' => $item['logged_username'],
                'idUcoip' => $ucoip['id_usuario'],
                'correo' => $ucoip['correo']
            ];
        }

        foreach ($formatData as $item) {

            $inventario = $this->hwService->storeHardware($item);

            if (!empty($item['idUcoip'])) {

                $this->resguardoService->asignarRecurso(
                    $inventario->id,
                    null,
                    $item['idUcoip']
                );

                $this->hwService->updateEstatusHardware(
                    $inventario->id,
                    2
                );
            }

            $this->info(
                "Equipo importado: {$item['nombre_equipo']}"
            );
        }

        $this->info('Importación finalizada correctamente.');

        return Command::SUCCESS;
    }

    public function findUcoipGlpi($ucoip, $nombreSite){
        $empresa = ModelsCatEmpresas::where('nombre', 'like', '%' . $nombreSite . '%')->first();

        $data = [
            'id_usuario' => null,
            'id_empresa' => null ?? 15,
            'correo' => null
        ];

        if($empresa){
            $usuario = DB::connection('intranet')->select('CALL SP_GetUsuarioEmail(?)', [$ucoip.'@'.$empresa->dominio]);
            $data = [
                'id_usuario' => $usuario[0]->id ?? null,
                'id_empresa' => $empresa->id ?? 15,
                'correo' => $ucoip.'@'.$empresa->dominio
            ];
        }

        return $data;
    }
}