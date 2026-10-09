<?php

namespace Modules\ParqueVehicular\Services;

use Modules\ParqueVehicular\Models\EventosProvOrden;

class EventosOsService
{
    public function inciarEvento( $ordenProvId, $tipoEvento, $fechaInicio =  null){
        $evento = new EventosProvOrden();
        $evento->pv_ordenes_proveedores_id = $ordenProvId;
        $evento->inicio_evento = $fechaInicio ?? now();
        $evento->pv_cat_eventos_id = $tipoEvento;
        $evento->save();

        return $evento;
    }

    public function finalizarEvento($idEvento, $observaciones = '', $fechaFin =  null){

        $evento = EventosProvOrden::find($idEvento);
        if($evento){
            $evento->fin_evento = $fechaFin ?? now();
            $evento->observaciones = $observaciones;
            $evento->save();
            return $evento;
        }

        return false;
    }


    public function finalizarUltimoEvento($ordenProvId, $observaciones = '', $fechaFin = null) {
    $evento = EventosProvOrden::where('pv_ordenes_proveedores_id', $ordenProvId)
        ->whereNull('fin_evento')
        ->latest('id')
        ->first();

    if ($evento) {
        $evento->fin_evento = $fechaFin ?? now();
        if (!empty($observaciones)) {
            $evento->observaciones = $observaciones;
        }
        $evento->save();

        return $evento;
    }

    return false;
}

}
