<?php

namespace Modules\Nissan\Services;

use App\Enums\EstatusComisionesAutos;
use App\Enums\EstatusSolicitud;
use Modules\Nissan\Models\DatosVenta;

class ComisionesService
{

    /**
     *HELPERS 
     */

    /**
     * Recupera el estatus en texto para comisiones de agencias
     */
    public function getLabelStatus($estatus){
        return EstatusComisionesAutos::label($estatus);
    }

    /**
     * Recupera los datos de venta por un parametro
     */
    public function getVentaByParam($param, $value){
       return DatosVenta::where($param, $value)->first();
    }

    /**
     * Adapta el numero de agencia par qu concuerde con los de la base de datos
     */
    public function parseAgencia($intercompania){
                    // intercompanias => Azcapo   Campestre  Universidad    Agencia ingresada
        return match($intercompania){ '7051' => '730', '712' => '714', '710' => '710', '333' => null,
                                    '7064' => '1','7063' => '3','7062' => '2','7061' => '4',
                                    default => $intercompania};
    }

    /**
     * calcula es el estatus al que debe de avanzar la comisiones
     */
    public function avanzarEst($estatusActual)
    {
        if ($estatusActual != EstatusComisionesAutos::PAGADA 
            && $estatusActual != EstatusComisionesAutos::RECHAZADA) {  
            if ($estatusActual == EstatusComisionesAutos::POR_AUTORIZAR) {
                $nuevoEstatus = EstatusComisionesAutos::AUTORIZADA;
            } else {
                $nuevoEstatus = $estatusActual + 1;
            }
        } else {
            $nuevoEstatus = $estatusActual;
        }
        return $nuevoEstatus;
    }

    public function devolverEst($estatusActual)
    {
        if ($estatusActual != EstatusComisionesAutos::PAGADA 
            && $estatusActual != EstatusComisionesAutos::RECHAZADA) {  
            $nuevoEstatus =  $estatusActual - 1;
        } else {
            $nuevoEstatus = $estatusActual;
        }
        return $nuevoEstatus;
    }


}
