<?php

namespace Modules\Compras\Services;

use Modules\Compras\Models\CatalogoPrecios;

class CatalgoPreciosService
{
    public function getPrecioProductoProveedor($listIdProductos){
        $preciosProveedores =  CatalogoPrecios::whereIn('com_productos_id', $listIdProductos)->get();

        return $preciosProveedores;
    }
}
