<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Http\Resources\NissanMesResource;

use App\Http\Controllers\GetMonthYearController;
use App\Http\Controllers\Controller;

/**
 * Modelos
 */
use App\Models\VentasPostVenta;
use App\Models\DatosGenerales;
use App\Models\CostosFinancierosPrestamos;
use App\Models\Complementos;
use App\Models\UtilidadArea;
use App\Models\OrdenesUnidades;
use Illuminate\Database\Eloquent\Relations\Relation;

class AgenciasController extends Controller
{
    private $monthYear;

    public function __construct(GetMonthYearController $monthYear)
    {
        $this->monthYear = $monthYear;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //$mes = $this->monthYear->getMonth();
        $mes = '05';
        //$mesAnt = $this->monthYear->getMonthPrev();
        $mesAnt = '04';
        //$anio = $this->monthYear->getYear();
        $anio = '2024';
        //$anioAnt = $this->monthYear->getYearPrev();
        $anioAnt = '2023';

        $nissanMes =  NissanMesResource::collection(DB::select('call Dashboard.SP_GetDataMesNissan(' . $mes . ',' . $anio . ')'));
        $nissanMesAnt =  NissanMesResource::collection(DB::select('call Dashboard.SP_GetDataMesNissan(' . $mesAnt . ',' . $anio . ')'));
        $nissanAnioAnt =  NissanMesResource::collection(DB::select('call Dashboard.SP_GetDataMesNissan(' . $mes . ',' . $anioAnt . ')'));

        $data = [
            'mes' => $nissanMes,
            'mesAnt' => $nissanMesAnt,
            'anioAnt' => $nissanAnioAnt
        ];

        if (count($nissanMes) > 0) {
            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $data
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No se tiene información captura.',
                'data' => []
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard::create');
    }

    /**
     * Guarda los registros de la tabla captura Nissan
     * Tablas que afecta
     * ------------------------------------------------
     * ordenes_unidades, ventas_post_venta, datos_generales
     * costos_financieros_prestamos, complementos, utilidad_area
     */
    public function store(Request $request)
    {
        $dataMesAgencias = $request->input('dataMesAgencias'); // Recibe datos en crudo
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        $fecha = sprintf('%s-%02d-01', $anio, $mes);

        // Relación tablas sección
        $relTablas = [
            "UNIDADES VENDIDAS" => "ordenes_unidades",
            "ORDENES DE SERVICIO" => "ordenes_unidades",
            "VENTAS DE POST VENTA" => "ventas_post_venta",
            "TOTAL DE GASTOS OPERATIVOS" => "datos_generales",
            "COSTO FINANCIERO CONSOLIDADO" => "costos_financieros_prestamos",
            "BONOS MARCA" => "complementos",
            "UNO" => "datos_generales",
            "ACUMULADO PERSONAL CONSOLIDADO" => "datos_generales",
            "UTILIDAD POR AREA" => "utilidad_area",
        ];

        $relCampos = [
            "Nuevos" => "nuevos",
            "UB Nuevos" => "utilidad_nuevos",
            "Flotillas" => "flotillas",
            "UB Flotillas" => "utilidad_flotillas",
            "Seminuevos" => "seminuevos",
            "UB Seminuevos" => "utilidad_seminuevos",
            "Ordenes de servicios" => "servicio",
            "UB O. servicios" => "utilidad_servicio",
            "Ordenes de HyP" => "hyp",
            "UB Ordenes de HyP" => "utilidad_hyp",
            "Ventas Servicio" => "ventas_servicio",
            "Total Ventas Refacciones" => "total_ventas_ref",
            "Refacciones Servicio" => "refacciones_servicio",
            "Refacciones HyP" => "refacciones_hyp",
            "Refacciones Mostrador" => "refacciones_mostrador",
            "Total de Gastos Operativos" => "gasto",
            "CNuevos" => "nuevos",
            "CFlotillas" => "utilidad_nuevos",
            "Refacciones" => "refacciones",
            "Bajio" => "bajio",
            "Intercias" => "intercias",
            "Bonos Marca" => "bonos",
            "UNO" => "uno",
            "Personal" => "personal",
            "Area Comercial" => "area_comercial",
            "Area Postventa" => "area_postventa",
        ];

        $relAgencias = [
            "Campestre" => 22,
            "'Automotriz'" => 23,
            "Insurgentes" => 24,
            "Universidad" => 25,
        ];

        // Procesamiento del array a json
        $jsonData = [];
        $seccion = "";

        foreach ($dataMesAgencias as $row) {
            if (count($row) === 1) {
                $seccion = trim($row[0]['value']);
            } elseif ($seccion) {
                $concepto = trim($row[0]['value']);

                foreach (array_slice($row, 1) as $index => $cell) {
                    $agencia = trim($request->input('headers')[$index + 1]);

                    if (strtolower($agencia) !== "total") {
                        $dbAgencia = $relAgencias[$agencia] ?? $agencia;
                        $dbSeccion = $relTablas[$seccion] ?? $seccion;
                        $dbCampos = $relCampos[$concepto] ?? $concepto;
                        $value = trim($cell['value'] ?? "");

                        if (!isset($jsonData[$dbAgencia])) {
                            $jsonData[$dbAgencia] = [];
                        }
                        if (!isset($jsonData[$dbAgencia][$dbSeccion])) {
                            $jsonData[$dbAgencia][$dbSeccion] = ["fecha" => $fecha];
                        }

                        $jsonData[$dbAgencia][$dbSeccion][$dbCampos] = $value;
                    }
                }
            }
        }

        // Insertar los datos en la base de datos
        foreach ($jsonData as $agenciaId => $secciones) {
            foreach ($secciones as $seccion => $valores) {
                switch ($seccion) {
                    case 'ordenes_unidades':
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
            'status' => 'success',
            'message' => 'Registros guardados correctamente',
            'data' => []
        ]);
    }

    /**
     * Actualiza los datos si existen y los crea si no existen
     */
    public function updateAgenciaNissan(Request $request)
    {
        $dataMesAgencias = $request->input('dataMesAgencias');
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        $fecha = sprintf('%s-%02d-01', $anio, $mes);

        if (empty($dataMesAgencias)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se recibieron datos para actualizar',
            ]);
        }

        /**-------------------------------------------------------------
         * Inicia proceso de formateo de datos
        ---------------------------------------------------------------- */
        // Relacion seccion->tabla
        $relTablas = [
            "UNIDADES VENDIDAS" => "ordenes_unidades",
            "ORDENES DE SERVICIO" => "ordenes_unidades",
            "VENTAS DE POST VENTA" => "ventas_post_venta",
            "TOTAL DE GASTOS OPERATIVOS" => "datos_generales",
            "COSTO FINANCIERO CONSOLIDADO" => "costos_financieros_prestamos",
            "BONOS MARCA" => "complementos",
            "UNO" => "datos_generales",
            "ACUMULADO PERSONAL CONSOLIDADO" => "datos_generales",
            "UTILIDAD POR AREA" => "utilidad_area",
        ];
         //Relacion campo->campo_tabla
        $relCampos = [
            "Nuevos" => "nuevos",
            "UB Nuevos" => "utilidad_nuevos",
            "Flotillas" => "flotillas",
            "UB Flotillas" => "utilidad_flotillas",
            "Seminuevos" => "seminuevos",
            "UB Seminuevos" => "utilidad_seminuevos",
            "Ordenes de servicios" => "servicio",
            "UB O. servicios" => "utilidad_servicio",
            "Ordenes de HyP" => "hyp",
            "UB Ordenes de HyP" => "utilidad_hyp",
            "Ventas Servicio" => "ventas_servicio",
            "Total Ventas Refacciones" => "total_ventas_ref",
            "Refacciones Servicio" => "refacciones_servicio",
            "Refacciones HyP" => "refacciones_hyp",
            "Refacciones Mostrador" => "refacciones_mostrador",
            "Total de Gastos Operativos" => "gasto",
            "CNuevos" => "nuevos",
            "CFlotillas" => "utilidad_nuevos",
            "Refacciones" => "refacciones",
            "Bajio" => "bajio",
            "Intercias" => "intercias",
            "Bonos Marca" => "bonos",
            "UNO" => "uno",
            "Personal" => "personal",
            "Area Comercial" => "area_comercial",
            "Area Postventa" => "area_postventa",
        ];
        //Relacion agencia->id_agencia
        $relAgencias = [
            "Campestre" => 22,
            "'Automotriz'" => 23,
            "Insurgentes" => 24,
            "Universidad" => 25,
        ];

        // Procesamiento del array a json
        $jsonData = [];
        $seccion = "";

        foreach ($dataMesAgencias as $row) {
            if (count($row) === 1) {
                $seccion = trim($row[0]['value']);
            } elseif ($seccion) {
                $concepto = trim($row[0]['value']);

                foreach (array_slice($row, 1) as $index => $cell) {
                    $agencia = trim($request->input('headers')[$index + 1]);

                    if (strtolower($agencia) !== "total") {
                        $dbAgencia = $relAgencias[$agencia] ?? $agencia;
                        $dbSeccion = $relTablas[$seccion] ?? $seccion;
                        $dbCampos = $relCampos[$concepto] ?? $concepto;
                        $value = trim($cell['value'] ?? "");

                        if (!isset($jsonData[$dbAgencia])) {
                            $jsonData[$dbAgencia] = [];
                        }
                        if (!isset($jsonData[$dbAgencia][$dbSeccion])) {
                            $jsonData[$dbAgencia][$dbSeccion] = ["fecha" => $fecha];
                        }

                        $jsonData[$dbAgencia][$dbSeccion][$dbCampos] = $value;
                    }
                }
            }
        }
        /**-------------------------------------------------------------
         * Finaliza el proceso de formateo de datos
         * Inicia proceso actualizar o insertar los datos en la base de datos
        ---------------------------------------------------------------- */
        foreach ($jsonData as $agenciaId => $secciones) {
            foreach ($secciones as $seccion => $valores) {
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
                    $model = $modelClass::where('sucursales_id', $agenciaId)->where('fecha', $valores['fecha'])->first();

                    if ($model) {
                        $model->update($valores);
                    } else {
                        $modelClass::create(array_merge(['sucursales_id' => $agenciaId], $valores));
                    }
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Registros actualizados correctamente',
            'data' => []
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
    public function update(Request $request, $id)
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

    public function showAgenciasNissan($mes, $anio)
    {
        $data =  NissanMesResource::collection(DB::select('call Dashboard.SP_GetDataMesNissan(' . $mes . ',' . $anio . ')'));


        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $data
        ]);
    }

    /**
     * Recupera los datos del Store Procedure
     * Formatea los datos a json
     * Genera una estructura para mostrar los datos en el fronted
     */
    public function getDataGridAgencia($mes, $anio)
    {
        $datos = DB::select('call Dashboard.SP_GetDataMesNissan(' . $mes . ',' . $anio . ')');
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
                    ['value' => "nuevos", 'colspan' => 1],//Cambiar el valor que regresa de la bd
                    ['value' => "flotillas", 'colspan' => 1],//Cambiar el valor que regresa de la bd
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
            $mapaCampos = [
                "nuevos" => "Nuevos",
                "utilidad_nuevos" => "UB Nuevos",
                "utilidad_nuevos" => "UB Nuevos",
                "flotillas" => "Flotillas",
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
                "bonos" => "BONOS MARCA",
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
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $datos, //Data que se ve en fronted
                'size' => $tamanioDatos, //Tamaño para validar
            ]);
        }
    }
}
