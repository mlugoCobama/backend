<?php

namespace Modules\ParqueVehicular\Services;

use Modules\ParqueVehicular\Models\OrdenServicio;

class OrdenServicioCobService
{

    public function generarFolioOS(string $tipo)
    {
        // $tipo = strval(1);
        $sufijos = ['1' => "OSE-",'2' => "OSI"];

        $ultimaOrden = OrdenServicio::active()
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


    public function updateEstatus($id, $estatus){
        $ordenServicio = OrdenServicio::find($id);
        if($ordenServicio){
            $ordenServicio->estatus = $estatus;
            $ordenServicio->save();
        }
    }

}
