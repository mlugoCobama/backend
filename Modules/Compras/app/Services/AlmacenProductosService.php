<?php

namespace Modules\Compras\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Models\DetalleEntrega;
use Modules\Compras\Models\AlmacenProducto;
use Modules\Compras\Models\EntregaTecnicos;
use Modules\Compras\Models\MovimientosAlmacen;

class AlmacenProductosService
{
    /**
     * Crea un nuevo registro de stock para un producto en un almacén o empresa específica.
     *
     * @param mixed $almEmpresa Identificador de la empresa o almacén.
     * @param string $idProducto Identificador del producto (llave foránea).
     * @param float $stock Stock inicial con el que se registrará (por defecto 0).
     * @param float $min Stock mínimo permitido para alertas (por defecto 0).
     * @param float|null $max Stock máximo permitido (por defecto null).
     * @return AlmacenProducto Retorna la instancia del modelo recién creada y guardada.
     */
    public function createAlmacenProducto($almEmpresa, $idProducto, $stock = 0, $min = 0, $max = null){
        $almacenProducto                    =  new AlmacenProducto();
        $almacenProducto->stock             = $stock;
        $almacenProducto->stock_minimo      = $min;
        $almacenProducto->stock_maximo      = $max;
        $almacenProducto->almacen_empresa   = $almEmpresa;
        $almacenProducto->com_productos_id  = $idProducto;

        $almacenProducto->save();

        return $almacenProducto;
    }

    /**
     * Actualiza el stock de un producto dentro de un almacén o empresa específica
     * basándose en el tipo de movimiento (salida, entrada o devolución).
     *
     * Tipos de movimientos:
     * - 's' = Salida (-)
     * - 'e' = Entrada (+)
     * - 'd' = Devolución (+)
     *
     * @param mixed $almEmpresa Identificador de la empresa o almacén.
     * @param string $idProducto Identificador del producto.
     * @param float $cantidad Cantidad a sumar o restar del stock actual.
     * @param string $tipo Tipo de movimiento ('s', 'e', 'd').
     * @return AlmacenProducto|false Retorna el modelo actualizado o false si el stock resultante es negativo.
     */
    public function updateStockAlmPro($almEmpresa, $idProducto, $cantidad, $tipo = 's'){

        $almacenProducto = AlmacenProducto::where('almacen_empresa', $almEmpresa)
                                            ->where('com_productos_id', $idProducto)
                                            ->first();
        if (!$almacenProducto) {
            $almacenProducto = $this->createAlmacenProducto($almEmpresa, $idProducto);
        }

        $stock = $almacenProducto->stock ?? 0;

        $nuevoStock =  match ($tipo) {
            's'     => (float)$stock - (float)$cantidad,
            'e'     => (float)$stock + (float)$cantidad,
            'd'     => (float)$stock + (float)$cantidad,
            default => (float)$stock
        };

        if ($nuevoStock >= 0) {
            $almacenProducto->stock = $nuevoStock;
            $almacenProducto->save();

            return $almacenProducto;
        }



        return false;
    }


    /**
     * Almacena el registro histórico de un movimiento realizado en el almacén
     * (sea entrada, salida o relacionado a un folio/técnico).
     *
     * @param float $cantidad Cantidad del producto involucrada en el movimiento.
     * @param int $idAlmProd ID del registro en `com_almacen_productos`.
     * @param string $tipo Tipo de movimiento ('s' para salida, 'e' para entrada).
     * @param int|null $idEntrada ID opcional relacionado a la entrada de almacén.
     * @param int|null $idSalida ID opcional relacionado a la salida de almacén o técnico.
     * @param string $observaciones Notas o comentarios adicionales del movimiento.
     * @param int|null $entrega ID del usuario que entrega el producto.
     * @param int|null $recibe ID del usuario que recibe el producto.
     * @return MovimientosAlmacen Retorna la instancia del movimiento recién creado.
     */
    public function storeMovimientoAlmacen($cantidad, $idAlmProd, $tipo = 's', $idEntrada = null, $idSalida = null,  $observaciones = '', $entrega = null, $recibe = null)
    {
        $movimiento = new MovimientosAlmacen();

        $movimiento->cantidad = $cantidad;
        $movimiento->tipo = $tipo;
        $movimiento->com_almacen_producto_id = $idAlmProd;
        $movimiento->observaciones = $observaciones;
        $movimiento->fecha = now();
        $movimiento->com_almacen_id = $idEntrada;
        $movimiento->id_usuario = $recibe;
        $movimiento->id_usuario_entrega = $entrega;
        $movimiento->com_id_entrega_tecnico = $idSalida;

        $movimiento->save();

        return $movimiento;
    }

    /**
     * ENTRADAS QUE SI USAN CATALOGO
     * Consulta que recupera las existencias de los almacenes de forma generalizada o filtrada.
     *
     * @param mixed|null $intercomania Código o ID de la empresa/almacen. Si es null, agrupa y suma el stock global.
     * @param mixed|null $tipo Filtro opcional por tipo de producto.
     * @param mixed|null $subcategoria Filtro opcional por ID de subcategoría.
     * @param mixed|null $categoria Filtro opcional por ID de categoría (relación anidada).
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function queryExistencias($intercomania = null, $tipo = null, $categoria = null,  $subcategoria = null)
    {
        return AlmacenProducto::with('producto.unidadMedida', 'producto.subCategoria.categoria')
            ->when($intercomania, function ($query, $intercomania) {
                return $query->where('almacen_empresa', $intercomania);
            }, function ($query) {
                return $query->select('com_productos_id', DB::raw('SUM(stock) as stock'))
                            ->groupBy('com_productos_id');
            })

            ->when($tipo, function ($query, $tipo) {
                return $query->whereHas('producto.subCategoria.categoria', function ($q) use ($tipo) {
                    $q->where('tipo', $tipo);
                });
            })

            ->when($subcategoria, function ($query, $subcategoria) {
                return $query->whereHas('producto', function ($q) use ($subcategoria) {
                    $q->where('com_subcategoria_id', $subcategoria);
                });
            })

            ->when($categoria, function ($query, $categoria) {
                return $query->whereHas('producto.subCategoria', function ($q) use ($categoria) {
                    $q->where('com_categoria_id', $categoria);
                });
            })

            ->get();
    }

    // ENTRADAS QUE NO USAN CATALOGO DE PRODUCTOS
    public function queryExistenciasB($tipo = null, $intercompania = null, $categoria = null)
    {
        $almacen = DB::select('CALL sp_query_existencias_b(?,?,?)', [$tipo, $intercompania, $categoria]);

        return $almacen;
    }

    /**
     * Genera un folio único y consecutivo para la entrega de técnicos basado en el tipo de salida.
     * Prefijos: 1 => ECG, 2 => EMT, 3 => ERT.
     *
     * @int $tipo Tipo de salida que determina el prefijo del folio.
     * @return string Folio formateado (Ej. ERT-00001).
     */
    private function generarFolio($tipo)
    {
        $prefijos = [
            1 => 'ECG',
            2 => 'EMT',
            3 => 'ERT',
        ];

        $prefijo = $prefijos[$tipo] ?? 'ECG';

        $ultimoFolio = EntregaTecnicos::where('folio', 'like', $prefijo . '-%')
            ->orderBy('id', 'desc')
            ->first();

        if ($ultimoFolio) {
            $numero = (int) str_replace($prefijo . '-', '', $ultimoFolio->folio);
            $nuevoNumero = $numero + 1;
        } else {
            $nuevoNumero = 1;
        }

        return $prefijo . '-' . str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Crea y registra el encabezado principal de la entrega de recursos al técnico.
     *
     * @param mixed $uEntrega Usuario que realiza la entrega.
     * @param mixed $uRecibe ID del técnico que recibe los recursos.
     * @param int $tipoSalida Tipo de salida para la generación del folio.
     * @return EntregaTecnicos El registro de entrega creado.
     */
    public function storeEntregaRecursos( $uEntrega, $uRecibe, $tipoSalida, $empresa){
        $entrega = new EntregaTecnicos();
        $entrega->folio = $this->generarFolio($tipoSalida);
        $entrega->usuario_entrega = $uEntrega;
        $entrega->tecnico_id = $uRecibe;
        $entrega->empresa = $empresa;
        $entrega->fecha = now();
        $entrega->tipo = $tipoSalida;
        $entrega->save();

        return $entrega;
    }


    /**
     * Procesa la salida de inventario de manera masiva para una lista de movimientos.
     *
     * @param int $tipoSalida Tipo de salida de recursos.
     * @param mixed $almEmpresa Identificador del almacén o empresa.
     * @param mixed $uEntrega Usuario que entrega.
     * @param mixed $uRecibe Usuario/técnico que recibe.
     * @param array $movimientos Lista de productos y cantidades a descontar.
     * @return void
     */
    public function storeSalidaInventario($tipoSalida, $almEmpresa, $uEntrega, $uRecibe, $movimientos)
    {
        return DB::transaction(function () use ($tipoSalida, $almEmpresa, $uEntrega, $uRecibe, $movimientos) {
        // Alamacena el registro de salida
        $entrega =  $this->storeEntregaRecursos($uEntrega, $uRecibe, $tipoSalida, $almEmpresa);

            foreach ($movimientos as $movimiento) {

                $almacenEmpresa = null;

                //si no pertence aun catalogo se registra la referencia que se uso para darle entrada al producto

                $idEntrada = !empty($movimiento['producto_id']) ? null : $movimiento['id'];
                // Si tiene id producto la salida se realiza en los almacenes
                if(!empty($movimiento['producto_id'])){
                    $almacenEmpresa = $this->updateStockAlmPro($almEmpresa, $movimiento['producto_id'], (float)$movimiento['cantidad'],  's');
                }else{
                    // si no la salida se realiza en la tabla de entrada
                     $this->actualizarExistencia($idEntrada, (float) $movimiento['cantidad'], -1);
                }

                $idAlmEmpresa = $almacenEmpresa ? $almacenEmpresa->id : null;
                $this->storeMovimientoAlmacen((float) $movimiento['cantidad'], $idAlmEmpresa, 's', $idEntrada, $entrega->id,'', $uEntrega, $uRecibe,);

            }

        return $entrega;

        });
    }

    public function actualizarExistencia($idAlmacen,  $valorEntrante, $tipo)
    {
        $itemAlmacen = DetalleEntrega::where('id', $idAlmacen)->first();
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


    /**
     * Recupera el historial de movimientos de los productos en el almacén.
     *
     * @param mixed|null $idProducto Filtra todos los movimientos de un producto en todos los almacenes/empresas.
     * @param mixed|null $idAlmEmpresa Filtra exclusivamente sobre un registro específico de almacén-producto (id de la tabla pivote).
     * @param string|null $tipoMovimiento Filtro opcional por tipo de movimiento ('e' para entrada, 's' para salida).
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function queryMovimientosAlmacen($idProducto = null, $idAlmEmpresa = null, $tipoMovimiento = null)
    {
        return MovimientosAlmacen::with(['almacenProducto.producto.unidadMedida'])

            ->when($idAlmEmpresa, function ($query, $idAlmEmpresa) {
                return $query->where('com_almacen_producto_id', $idAlmEmpresa);
            })

            ->when(!$idAlmEmpresa && $idProducto, function ($query) use ($idProducto) {
                return $query->whereHas('almacenProducto', function ($q) use ($idProducto) {
                    $q->where('com_productos_id', $idProducto);
                });
            })

            ->when($tipoMovimiento, function ($query, $tipoMovimiento) {
                return $query->where('tipo', $tipoMovimiento);
            })

            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getSalidaAlmacen($idSalida)
    {

        return EntregaTecnicos::with('movimientosEntrega.entrada.detalle.unidadMedida', 'movimientosEntrega.almacenProducto.producto.unidadMedida')->find($idSalida);

    }

    public function formatDataToReporte($entrega){
        $detallesFormateados = $entrega->movimientosEntrega->map(function ($movimiento) {

            // CASO A: Viene por la relación 'entrada -> detalle'
            if ($movimiento->entrada && $movimiento->entrada->detalle) {
                $detalle = $movimiento->entrada->detalle;
                return [
                    'cantidad'      => $movimiento->cantidad ?? 0,
                    'unidad'        => $detalle->unidadMedida->abreviatura ?? 'Pza',
                    'descripcion'   => $detalle->descripcion ?? 'Sin descripción',
                    'observaciones' => $detalle->observaciones ?? '',
                ];
            }

            // CASO B: Viene por la relación 'almacenProducto -> producto'
            if ($movimiento->almacenProducto && $movimiento->almacenProducto->producto) {
                $producto = $movimiento->almacenProducto->producto;
                return [
                    'cantidad'      => $movimiento->cantidad ?? 0,
                    'unidad'        => $producto->unidadMedida->abreviatura ?? 'Pza',
                    'descripcion'   => $producto->descripcion_planta ?? $producto->descripcion_proveedor ?? 'Sin descripción',
                    'observaciones' => $movimiento->observaciones ?: ($producto->observaciones ?? ''),
                ];
            }

            // Fallback por seguridad si viniera vacío
            return [
                'cantidad'      => $movimiento->cantidad ?? 0,
                'unidad'        => 'Pza',
                'descripcion'   => 'Recurso genérico',
                'observaciones' => $movimiento->observaciones ?? '',
            ];
        });

        // 3. Preparar el arreglo final de datos para el PDF
        $data = [
            'folio'       => $entrega->folio,
            'fecha'       => date('d/m/Y H:i', strtotime($entrega->fecha)),
            'entrego'     => $entrega->usuarioEntregaRelacion->name ?? ('ID Usuario: ' . $entrega->usuario_entrega),
            'recibe'      => $entrega->tecnicoRelacion->name ?? ('ID Técnico: ' . $entrega->tecnico_id),
            'detalles'    => $detallesFormateados->toArray(),
            'anotaciones' => 'Entrega correspondiente al folio ' . $entrega->folio
        ];

        return $data;
    }
}
