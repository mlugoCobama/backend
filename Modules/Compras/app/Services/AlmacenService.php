<?php

namespace Modules\Compras\Services;

use Carbon\Carbon;
use Modules\Compras\Models\AlmacenCompras;
use Modules\Compras\Models\EntregaTecnicos;
use Modules\Compras\Models\MovimientosAlmacen;

class AlmacenService
{
    /**
     * Almacena un movimiento en el almacen
     * $tipo's' para salida ó 'e' para entrada
     */
    public function storeMovimientoAlmacen($data,  $usuario_realiza, $usuario_entrega, $idEntrega = null, $tipo = 's')
    {
        $movimiento = new MovimientosAlmacen();
        $movimiento->cantidad = $data['cantidad'];
        $movimiento->tipo = $tipo;
        // $movimiento->observaciones = $data['observaciones'] ?? null;
        $movimiento->fecha = now();
        $movimiento->com_almacen_id = $data['id'];
        $movimiento->id_usuario = $usuario_realiza;
        $movimiento->id_usuario_entrega = $usuario_entrega;
        $movimiento->com_id_entrega_tecnico = $idEntrega;
        $movimiento->save();
        return $movimiento;
    }

    public function storeEntregaTecnico( $usuario_realiza, $usuario_entrega){
        $entrega = new EntregaTecnicos();
        $entrega->folio = $this->generarFolio();
        $entrega->usuario_entrega = $usuario_realiza;
        $entrega->tecnico_id = $usuario_entrega;
        $entrega->fecha = now();
        $entrega->save();

        return $entrega;

    }

    private function generarFolio()
    {
        // Obtener el último folio registrado

        $ultimoFolio = EntregaTecnicos::orderBy('id', 'desc')->first();

        if ($ultimoFolio) {
            // Extraer la parte numérica del folio
            $numero = (int) str_replace('ERT-', '', $ultimoFolio->folio);
            $nuevoNumero = $numero + 1;
        } else {
            // Si no existe ningún folio, empezamos en 1
            $nuevoNumero = 1;
        }

        // Formatear con ceros a la izquierda (ejemplo: ETI-00001)
        $nuevoFolio = 'ERT-' . str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT);

        return $nuevoFolio;
    }


    /**
     * Actualiza la existencia en la tabla almacen
     * $tipo  '-1' para salida ó '1' para entrada
     */
    public function actualizarExistencia($idAlmacen,  $valorEntrante, $tipo)
    {
        $itemAlmacen = AlmacenCompras::where('id', $idAlmacen)->first();
        if ($itemAlmacen) {
            $existencia = $itemAlmacen->existencia + ($valorEntrante * $tipo); 
        } else {
            $existencia = ($valorEntrante * $tipo);
        }
        $itemAlmacen->existencia = $existencia; 
        $itemAlmacen->fecha_actualizacion = Carbon::now()->format('Y-m-d'); 
        $itemAlmacen->save(); 

        return $itemAlmacen;
    }


}