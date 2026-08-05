<?php

namespace Modules\Ucoip\Services;

use Modules\Ucoip\Models\CatEmpresas;
use Modules\Ucoip\Models\Ucoip;

class UcoipService{

public function storeUcoip( $data){
}

/**
 * Busca un registro de Ucoip basado en el número de Ucoip proporcionado, el nombre de la empresa y la división.
 *
 * @param string $ucoip El número de Ucoip a buscar.
 * @param string $empresa El nombre de la empresa para filtrar.
 * @param string $division La división para filtrar.
 */
public function findUcoip($ucoip, $empresa, $division ){

    $empresa = CatEmpresas::where('nombre', 'like', '%'.$empresa.'%')->where('division',$division)->latest()->first();
    $resultado =  null;

    if($empresa){
        $resultado = Ucoip::where('ucoip', $ucoip)->where('cat_empresa_id', $empresa->id)->first();
    }

    return $resultado;
}
}
