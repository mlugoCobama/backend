<?php

namespace Modules\Ucoip\Services;

use App\Enums\EstatusActivos;
use App\Enums\EstatusAsignaciones;
use Modules\Ucoip\Models\CatSoftware;
use Modules\Ucoip\Models\Software;
use Modules\Ucoip\Models\SoftwareUcoip;

class SoftwareService{
    /**
     * Stores a new software record in the database.
     *
     * @param string $empresa The company associated with the software.
     * @param string $version The version of the software.
     * @param string|null $licencia The license information. Defaults to 'N/D' if not provided.
     * @param string|null $observaciones Additional observations about the software. Defaults to an empty string if not provided.
     * @param int $cat_software_id The category ID associated with the software.
     * @param string $tipo_licencia The type of license.
     * @param string|null $cuenta The account information for the software. Defaults to an empty string if not provided.
     * @param string|null $pass_cuenta The password for the account. Defaults to an empty string if not provided.
     * @param string|null $fecha_adquisicion The acquisition date of the software. Defaults to the current date and time if not provided.
     * @param string|null $estatus The status of the software. Defaults to EstatusActivos::DISPONIBLE if not provided.
     */
     public function storeSoftware($empresa,
     $version, $licencia, $observaciones,
     $cat_software_id, $tipo_licencia, $cuenta,
     $pass_cuenta, $fecha_adquisicion, $estatus){
        $software = Software::create([
                "empresa" => $empresa,
                "version" => $version,
                "licencia" => $licencia ?? 'N/D' ,
                "observaciones" => $observaciones ?? '',
                "cat_software_id" => $cat_software_id,
                "tipo_licencia" => $tipo_licencia ,
                "cuenta" => $cuenta ?? '',
                "pass_cuenta" => $pass_cuenta ?? '',
                "fecha_adquisicion" => $fecha_adquisicion ??  now(),
                "estatus" => $estatus ?? EstatusActivos::DISPONIBLE
            ]);

        return $software;
    }

    /**
     * Updates an existing software record in the database.
     *
     * @param int $id The ID of the software to update.
     * @param string $empresa The company associated with the software.
     * @param string $version The version of the software.
     * @param string|null $licencia The license information. Defaults to 'N/D' if not provided.
     * @param string|null $observaciones Additional observations about the software. Defaults to an empty string if not provided.
     * @param int $cat_software_id The category ID associated with the software.
     * @param string $estatus The status of the software. Defaults to EstatusActivos::DISPONIBLE if not provided.
     */
    public function updateSoftware(
        $id,
        $empresa,
        $version,
        $licencia,
        $observaciones,
        $cat_software_id,
        $estatus,
        $tipo_licencia,
        $cuenta,
        $pass_cuenta,
        $fecha_adquisicion
    ){
        $software = Software::where('id', $id)->first();
        if($software){
            $software->update([
                "empresa" => $empresa,
                "version" => $version,
                "licencia" => $licencia ?? 'N/D',
                "observaciones" => $observaciones ?? '',
                "cat_software_id" => $cat_software_id ,
                "estatus" => $estatus,
                "tipo_licencia" => $tipo_licencia,
                "cuenta" => $cuenta ?? '',
                "pass_cuenta" => $pass_cuenta ?? '',
                "fecha_adquisicion" => $fecha_adquisicion ?? now()
            ]);

            return $software;
        }
}

    /**
     * Assigns a software to a user.
     *
     * @param int $idUcoip The ID of the UCOIP record.
     * @param int $idSoftware The ID of the software record to assign.
     * @return void
     */
    public function asignarSoftware($idUcoip, $idSoftware ){
        $asignacion = new SoftwareUcoip();
        $asignacion->ucoip_ucoip_id = $idUcoip;
        $asignacion->ucoip_software_id = $idSoftware;
        $asignacion->fecha_asignacion = now();
        $asignacion->save();

        $this->updateStatusSoftware($idSoftware, EstatusActivos::ASIGNADA);
    }

    /**
     * Updates the status of a software record.
     *
     * @param int $id The ID of the software record to update.
     * @param string $status The new status for the software.
     */
    public function updateStatusSoftware($id, $status){
        $software = Software::find($id);

        if($software){
            $software->estatus = $status;
            $software->save();
        }
    }

    /**
     * Obtiene el catálogo de software disponible.
     *
     * @param int $idEmpresa
     */
    public function getCatalogoDisponible($idEmpresa){
        $data = CatSoftware::with([
            'licenciasDisponible' => function ($query) use ($idEmpresa) {
                $query->active()->where('empresa', $idEmpresa);
            }
        ])->get();

        return $data;
    }

    /**
     * Obtiene las licencias disponibles por tipo y empresa.
     *
     * @param int $idEmpresa
     * @param int $tipo
     */
    public function getLicenciasDisponiblesTipo($idEmpresa, $tipo){
        $data =  Software::where(function ($query) {
            $query->active()->where('estatus', EstatusActivos::DISPONIBLE)
                  ->orWhere('tipo_licencia', 3);
        })
        ->where('cat_software_id', $tipo)
        ->where('empresa', $idEmpresa)
        ->get();

         return  $data;
    }

    public function asignacionSoftware($idUcoip, $idSoftware, $fecha_asignacion, )
    {
        $asignacion = new SoftwareUcoip();
        $asignacion->ucoip_ucoip_id = $idUcoip;
        $asignacion->ucoip_software_id = $idSoftware;
        $asignacion->fecha_asignacion = now();
        $asignacion->save();

        $this->updateStatusSoftware($idSoftware, EstatusActivos::ASIGNADA);

        return $asignacion;

    }

    public function finalizarAsignacion($idAsignacion)
    {
        $asignacion = SoftwareUcoip::find($idAsignacion);
        if($asignacion){
            $asignacion->fecha_retiro = now();
            $asignacion->activo = EstatusAsignaciones::FINALIZADA;
            $asignacion->save();

            $this->updateStatusSoftware($asignacion->ucoip_software_id, EstatusActivos::DISPONIBLE);
        }

       return $asignacion;
    }


}
