<?php

namespace Modules\Renault\Services;

use Modules\Renault\Models\RenCitasServicio;
use Modules\Renault\Models\RenEventosCita;

class CitaServicioService
{
    public function generarEvento($idCita, $tipoEvento){
        $evento =  new RenEventosCita();
       $evento->ren_cat_eventos_id = $tipoEvento;
       $evento->ren_citas_servicio_id = $idCita;
       $evento->inicio_evento = now();
       $evento->save();

       return $evento;
    }

    public function finalizarEvento($idEvento, $observaciones = null){
        $evento =  RenEventosCita::find($idEvento);
       $evento->fin_evento = now();
       $evento->observaciones = $observaciones;
       $evento->save();

       return $evento;
    }

    public function updateEstatus($idCita, $estatus){
        $estatusTexto = match ($estatus) {
            1 => 'AC', 2 => 'AT', 3 => 'AL',
            4 => 'CA', 5 => 'TE', 6 => 'EN',
            7 => 'FN',
            default => $estatus
        };

        RenCitasServicio::where('id', $idCita)->update([ 'estatus' => $estatusTexto]);
    }
}
