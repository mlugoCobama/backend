<?php

namespace Modules\Compras\Services;

use Illuminate\Support\Facades\DB;
use Modules\Compras\Models\SolicitudesCompra;

class ComprasGeneralesService{
    /**
     * Recupera las solicitudes de compra con el folio y total de la orden de compra
     * 
     * @param mixed $intercompania Numero de intercompania de la empresa
     * @param mixed $autoga Autorizacion de gerencia administrativa (0 o 1)
     * @param mixed $autogg Autorizacion de gerencia (0 o 1)
     * @param mixed $tipoSolicitud tipo de solicitud (1= compras, 2 = rt, null = ambas)
     * @param mixed $idUserObjetivo usuario objetivo (null = no aplica el filtro)
     */
    public function getSolicitudesCompras($intercompania, $autoga, $autogg, $tipoSolicitud, $idUserObjetivo, $tipoUsuario){
        return DB::select('CALL SP_GetSolicitudesCompras(?, ?, ?, ?, ?, ?)', [ $intercompania , $autoga, $autogg, $tipoSolicitud, $idUserObjetivo, $tipoUsuario]);
    }

    /** *********************************************************** 
     * Genera un nuevo folio consecutivo en base al ultimo folio
     *************************************************************/
    public function generarFolioSc($tipo)
    {
        // $tipo = strval(1);
        $sufijos = ['1' => "SC-",'3' => "SC-TI-", '4' => "SC-A-"];

        $ultimaOrden = SolicitudesCompra::active()
        ->where('tipo', $tipo)
        ->where('folio', 'LIKE', '%' . $sufijos[$tipo] . '%')
        ->orderBy('id', 'desc')
        // ->active()
        ->first('folio');
        if ($ultimaOrden) {
            $ultimoFolio = $ultimaOrden->folio;
            $numero = intval(substr($ultimoFolio, strlen($sufijos[$tipo]))) + 1;
        } else {
            $numero = 1;
        }
        $nuevoFolio = $sufijos[$tipo] . str_pad($numero, 5, '0', STR_PAD_LEFT);
        return  $nuevoFolio;
    }

}