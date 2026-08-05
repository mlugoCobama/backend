<?php

namespace Modules\Ucoip\Services;

use App\Enums\EstatusAsignaciones;
use Modules\Ucoip\Models\RecursosRedUcoip;


class RecursosRedService{

    public function asignarRecursoRed($hardwareId, $valor, $restrictivo, $observaciones, $idUcoip, $idTipo)
    {
        $asignacion                         =  new RecursosRedUcoip();
        $asignacion->equipo_id              =  $hardwareId ?? null;
        $asignacion->valor                  =  $valor;
        $asignacion->nivel_restrictivo      =  $restrictivo ?? null;
        $asignacion->observaciones          =  $observaciones ?? null;
        $asignacion->fecha_asignacion       =  now();
        $asignacion->ucoip_ucoip_id         =  $idUcoip;
        $asignacion->ucoip_cat_recursos_id  =  $idTipo;

        $asignacion->save();
    }

    public function removerRecursoRed($idAsignacion)
    {
        $asignacion = RecursosRedUcoip::find($idAsignacion);
        if($asignacion){
            $asignacion->fecha_retiro = now();
            $asignacion->activo = EstatusAsignaciones::INACTIVA;
            $asignacion->save();
        }
        $asignacion->save();
    }


}
