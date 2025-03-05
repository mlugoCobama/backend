<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Modelos
 */

use App\Models\VentasPostVenta;
use App\Models\DatosGenerales;
use App\Models\CostosFinancierosPrestamos;
use App\Models\Complementos;
use App\Models\UtilidadArea;
use App\Models\OrdenesUnidades;

class AgenciasRenaultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('dashboard::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard::create');
    }


    /**
     * Guarda los registros de la tabla captura Renault
     * -------------------------------------------------------
     * Tablas que afecta
     * -------------------------------------------------------
     * ordenes_unidades, ventas_post_venta, datos_generales
     * costos_financieros_prestamos, complementos, utilidad_area
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $sizePayload = count($data);
        //Valida que se hallan recibido datos
        if ($sizePayload < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debes pegar los datos en la zona indicada',
                'error' => 'No se agregaron datos'
            ]);
        }


        foreach ($data as $agenciaId => $secciones) {
            // Selecciona el modelo correspondiente a la sección
            foreach ($secciones as $seccion => $valores) {
                switch ($seccion) {
                    case 'ordenes_unidades':
                        /**
                         * Modelo::operacion(combinamos el valor de agenciasId como sucursales_id 
                         * dentro del array a guardar, junto con sus valores)
                         */
                        OrdenesUnidades::create(array_merge(['sucursales_id' => $agenciaId], $valores));
                        break;
                    case 'ventas_post_venta':
                        VentasPostVenta::create(array_merge(['sucursales_id' => $agenciaId], $valores));

                        break;
                    case 'datos_generales':
                        DatosGenerales::create(array_merge(['sucursales_id' => $agenciaId], $valores));

                        break;
                    case 'costos_financieros_prestamos':
                        CostosFinancierosPrestamos::create(array_merge(['sucursales_id' => $agenciaId], $valores));
                        break;
                    case 'complementos':
                        Complementos::create(array_merge(['sucursales_id' => $agenciaId], $valores));

                        break;
                    case 'utilidad_area':
                        UtilidadArea::create(array_merge(['sucursales_id' => $agenciaId], $valores));
                        break;
                }
            }
        }

        return response()->json([
            'status' =>  'success',
            'message' => 'Registros guardados correctamente',
            'data' => []
        ]);
    }


    public function updateAgenciaRenault(Request $request)
    {
        $data = $request->all();

        // Valida que se hayan recibido datos
        if (empty($data)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se recibieron datos para actualizar',
            ]);
        }

        foreach ($data as $agenciaId => $secciones) {
            foreach ($secciones as $seccion => $valores) {
                $model = null;

                // Selecciona el modelo correspondiente a la sección
                switch ($seccion) {
                    case 'ordenes_unidades':
                        $model = OrdenesUnidades::where('sucursales_id', $agenciaId)->where('fecha', $valores['fecha'])->first();
                        break;
                    case 'ventas_post_venta':
                        $model = VentasPostVenta::where('sucursales_id', $agenciaId)->where('fecha', $valores['fecha'])->first();
                        break;
                    case 'datos_generales':
                        $model = DatosGenerales::where('sucursales_id', $agenciaId)->where('fecha', $valores['fecha'])->first();
                        break;
                    case 'costos_financieros_prestamos':
                        $model = CostosFinancierosPrestamos::where('sucursales_id', $agenciaId)->where('fecha', $valores['fecha'])->first();
                        break;
                    case 'complementos':
                        $model = Complementos::where('sucursales_id', $agenciaId)->where('fecha', $valores['fecha'])->first();
                        break;
                    case 'utilidad_area':
                        $model = UtilidadArea::where('sucursales_id', $agenciaId)->where('fecha', $valores['fecha'])->first();
                        break;
                }

                // Si el registro existe, se actualiza; si no, se crea uno nuevo
                if ($model) {
                    $model->update($valores);
                } else {
                    $modelClass = match ($seccion) {
                        'ordenes_unidades' => OrdenesUnidades::class,
                        'ventas_post_venta' => VentasPostVenta::class,
                        'datos_generales' => DatosGenerales::class,
                        'costos_financieros_prestamos' => CostosFinancierosPrestamos::class,
                        'complementos' => Complementos::class,
                        'utilidad_area' => UtilidadArea::class,
                        default => null
                    };

                    if ($modelClass) {
                        $modelClass::create(array_merge(['sucursales_id' => $agenciaId], $valores));
                    }
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Registros actualizados correctamente',
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('dashboard::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('dashboard::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }


    /**
     * Recupera los datos del Store Procedure
     * Formatea los datos a json
     * Genera una estructura para mostrar los datos en el fronted
     */
    public function getDataGridAgencia($mes, $anio)
    {
        /**-----------------------------------------------------------------
         * !NOTA: CAMBIAR STORE PROCEDURE PARA QUE COINCIDA CON LA AGENCIA
         -------------------------------------------------------------------*/
        $datos = DB::select('call Dashboard.SP_GetDataMesRenault(' . $mes . ',' . $anio . ')');
        // Convierte $datos a un arreglo
        $datos = json_decode(json_encode($datos), true);
        $tamanioDatos = count($datos);

        /** 
         * Valida el tamaño de la respuesta
         * Si datos es mayor a 1 existen registros
         * SI es menor o igual 1 no existen registros del mes
         * -----------------------------------------------------
         * Se valida con uno porque le store procedure siempre 
         * devuelve un resultado de total
         * -----------------------------------------------------
         */
        if (count($datos) > 1) {
            //Estructura para devolver el array
            $estructura = [
                "UNIDADES VENDIDAS" => [
                    ['value' => "nuevos", 'colspan' => 1],
                    ['value' => "utilidad_nuevos", 'colspan' => 1],
                    ['value' => "flotillas", 'colspan' => 1],
                    ['value' => "utilidad_flotillas", 'colspan' => 1],
                    ['value' => "seminuevos", 'colspan' => 1],
                    ['value' => "utilidad_seminuevos", 'colspan' => 1]
                ],
                "ORDENES DE SERVICIO" => [
                    ['value' => "servicio", 'colspan' => 1],
                    ['value' => "utilidad_servicio", 'colspan' => 1],
                    ['value' => "hyp", 'colspan' => 1],
                    ['value' => "utilidad_hyp", 'colspan' => 1]
                ],
                "VENTAS DE POST VENTA" => [
                    ['value' => "ventas_servicio", 'colspan' => 1],
                    ['value' => "total_ventas_ref", 'colspan' => 1],
                    ['value' => "refacciones_servicio", 'colspan' => 1],
                    ['value' => "refacciones_hyp", 'colspan' => 1],
                    ['value' => "refacciones_mostrador", 'colspan' => 1],
                ],
                "TOTAL DE GASTOS OPERATIVOS" => [
                    ['value' => "gasto", 'colspan' => 1],
                ],
                "COSTO FINANCIERO CONSOLIDADO" => [
                    ['value' => "cnuevos", 'colspan' => 1],
                    ['value' => "cflotillas", 'colspan' => 1],
                    ['value' => "refacciones", 'colspan' => 1],
                    ['value' => "bajio", 'colspan' => 1],
                    ['value' => "intercias", 'colspan' => 1],
                ],
                "BONOS MARCAS" => [
                    ['value' => "bonos", 'colspan' => 1],
                ],
                "UNO" => [
                    ['value' => "uno", 'colspan' => 1],
                ],
                "ACUMULADO PERSONAL CONSOLIDADO" => [
                    ['value' => "personal", 'colspan' => 1],
                ],
                "UTILIDAD POR AREA" => [
                    ['value' => "area_comercial", 'colspan' => 1],
                    ['value' => "area_postventa", 'colspan' => 1],
                ],

            ];
            //Relación campos devueltos por la bd y 
            //nombres visuales de los campos
            //campoBD->TextoMostrado
            $mapaCampos = [
                "nuevos" => "Nuevos",
                "cnuevos" => "Nuevos",
                "utilidad_nuevos" => "UB Nuevos",
                "flotillas" => "Flotillas",
                "cflotillas" => "Flotillas",
                "utilidad_flotillas" => "UB Flotillas",
                "seminuevos" => "Seminuevos",
                "utilidad_seminuevos" => "UB Seminuevos",
                "servicio" => "Ordenes de servicios",
                "utilidad_servicio" => "UB O. servicios",
                "hyp" => "Ordenes de HyP",
                "utilidad_hyp" => "UB Ordenes de HyP",
                "ventas_servicio" => "Ventas Servicio",
                "total_ventas_ref" => "Total Ventas Refacciones",
                "refacciones_servicio" => "Refacciones Servicio",
                "refacciones_hyp" => "Refacciones HyP",
                "refacciones_mostrador" => "Refacciones Mostrador",
                "gasto" => "Gasto",
                "refacciones" => "Refacciones",
                "bajio" => "Bajio",
                "intercias" => "Intercias",
                "bonos" => "Bonos Marca",
                "uno" => "UNO",
                "personal" => "Personal",
                "area_comercial" => "Area Comercial",
                "area_postventa" => "Area Postventa",
            ];

            $resultado = [];

            foreach ($estructura as $seccion => $filas) {
                $resultado[] = [['value' => $seccion, 'colspan' => 6]];
                foreach ($filas as $fila) {
                    $nombreCampo = $mapaCampos[$fila['value']] ?? $fila['value'];
                    // $row = [$fila];
                    $row = [['value' => $nombreCampo, 'colspan' => 1]];
                    foreach ($datos as $dato) {
                        //Se utiliza number_format para que cuando se trate de numero se separe por comas correctamente 

                        $row[] = ['value' => number_format($dato[($fila['value'])] ?? '0'), 'colspan' => 1];
                    }
                    $resultado[] = $row;
                }
            }

            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $resultado, //Data que se ve en fronted
                'size' => $tamanioDatos, //Tamaño para validar
                'datos' =>  $datos
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $datos, //Data que se ve en fronted
                'size' => $tamanioDatos, //Tamaño para validar
                'datos' =>  $datos
            ]);
        }
    }
}
