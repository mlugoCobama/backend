<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\Dashboard\Transformers\DataAnualAgenciasResource;
use App\Http\Resources\NissanMesResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use DateTime;

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
    private array $meses = [
         1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
         5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
         9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
     ];
    /**
     * Display a listing of the resource.
     */
    public function index(string $sub_division, $mes, $anio){
         $mes = $mes - 1;

        $periodoBuscado = "$anio-$mes-01";
        $date = DateTime::createFromFormat('Y-m-d', $periodoBuscado);
        $fanio = $date->format('Y');
        $fmes = $date->format('m');
        $newPeriodo = "$fanio-$fmes-01";
        do{
            $dataAnio = DB::connection('dashboard')->select("call Dashboard.SP_GetDataAnualAgencias($anio, $sub_division)");
            (array)$arrDatos = $dataAnio;
            $arr_mesesDatos = array_map(function($registro) { return $registro->fecha; }, $arrDatos);

            $arr_mesesDatos1 = array_flip($arr_mesesDatos);

            $periodoExiste = isset($arr_mesesDatos1[$newPeriodo]); 
            
            if($periodoExiste === false){
                $anio = $anio - 1;
            }

        }while( count($dataAnio) < 1 );

        if($periodoExiste === false){

            $fecha = end($arr_mesesDatos);

            $data = $this->conjuntoDatos1($fecha, $sub_division,  $dataAnio);
            $fechaRecuperada = DateTime::createFromFormat('Y-m-d', $fecha);
            $mesRec = $fechaRecuperada->format('m');
            $anioRec = $fechaRecuperada->format('Y');
            $nombreMes = $this->meses[intval($mesRec)];

            return response()->json(
                
                [
                    'success' => true,
                    'message' => "No hay datos de este periodo en su lugar se muestran los de $nombreMes $anioRec",
                    'data' => $data
                ]

            );
        }else{
            $fecha = $periodoBuscado;
            
            $data = $this->conjuntoDatos1($fecha , $sub_division,  $dataAnio);
            $nombreMes = $this->meses[intval($mes)];
            return response()->json(
            [
                'success' => true,
                'message' => "Mostrando datos de $nombreMes $anio",
                'data' => $data,
            ]
        );
        }
    }

    private function conjuntoDatos1($fechaBusqueda, $sub_division,  $dataAnio){

        $fecha = DateTime::createFromFormat('Y-m-d', $fechaBusqueda);
        $anio =  $fecha->format('Y');
        $mes = $fecha->format('m');
        $anioAnt = $anio - 1;
        $fechaMesA = $fecha->modify('-1 month');
        $mesA = $fechaMesA->format('m');
        $anioA = $fechaMesA->format('Y');

            $dataMes =  $this->getDataMesRenault($mes, $anio);  
            $dataMesAnt =   $this->getDataMesRenault($mesA, $anioA);
            $dataAnioAnt =  $this->getDataMesRenault($mes, $anioAnt);
            $totalAnio =  DataAnualAgenciasResource::collection($dataAnio);
            $totalAnioAnt = $this->getDataAnualAgencias($anioAnt, $sub_division);
            $antInventarios = $this->getDataAntInventarios($mes, $anio, $sub_division);    
                $data = [
                    'mes' => $dataMes,
                    'mesAnt' => $dataMesAnt,
                    'anioAnt' => $dataAnioAnt,
                    'totalAnio' => $totalAnio,
                    'totalAnioAnt' => $totalAnioAnt,
                    'totalAnioAnt2' => [],
                    'antInventarios' => $antInventarios,
                    ];
                        
        return $data;

    }

     private function getDataMesRenault(int $mes, int $anio)
     {
         return NissanMesResource::collection(DB::select("call Dashboard.SP_GetDataMesRenault($mes, $anio)"));
     }

     private function getDataAnualAgencias(int $anio, string $subDivision)
     {
         return DataAnualAgenciasResource::collection(
             DB::connection('dashboard')->select("call Dashboard.SP_GetDataAnualAgencias($anio, $subDivision)")
         );
     }

     private function getDataAntInventarios(int $mes, int $anio, string $subDivision)
     {
         return DB::connection('dashboard')->select(
             "call Dashboard.SP_GetDataAntSemestralInventarios($mes, $anio, $subDivision)"
         );
     }



    public function create()
    {
        return view('dashboard::create');
    }
    
    private $relTablas = [
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

    private $relCampos = [
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

    private $relAgencias = [
            "Azcapotzalco" => 26,
            "Ecatepec" => 27,
            "Vallejo" => 28,
            "Pachuca" => 29,
        ];
    /**
     * Guarda los registros de la tabla captura Renault
     * ------------------------------------------------
     * Tablas que afecta: 
     * ordenes_unidades, ventas_post_venta, datos_generales
     * costos_financieros_prestamos, complementos, utilidad_area
     */
    public function store(Request $request)
    {
        $dataMesAgencias = $request->input('dataMesAgencias'); // Recibe datos en crudo
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        $fecha = sprintf('%s-%02d-01', $anio, $mes);

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
                        $dbAgencia = $this->relAgencias[$agencia] ?? $agencia;
                        $dbSeccion = $this->relTablas[$seccion] ?? $seccion;
                        $dbCampos = $this->relCampos[$concepto] ?? $concepto;
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
    public function updateAgenciaRenault(Request $request)
    {
        $dataMesAgencias = $request->input('dataMesAgencias'); //Recupera el array en crudo
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        $fecha = sprintf('%s-%02d-01', $anio, $mes);

        // Validar que se hallan recibido datos

        if (empty($dataMesAgencias)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se recibieron datos para actualizar',
            ]);
        }

        /**-------------------------------------------------------------
         * Inicia proceso de formateo de datos
        ---------------------------------------------------------------- */
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
                        $dbAgencia = $this->relAgencias[$agencia] ?? $agencia;
                        $dbSeccion = $this->relTablas[$seccion] ?? $seccion;
                        $dbCampos = $this->relCampos[$concepto] ?? $concepto;
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

    public function show($id)
    {
        return view('dashboard::show');
    }
    public function edit($id)
    {
        return view('dashboard::edit');
    }
    public function update(Request $request, $id)
    {
        //
    }
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

    //Relación tabla sección
    /**
    * $relTablas = [
    * "UNIDADES VENDIDAS" => "ordenes_unidades",
    * "ORDENES DE SERVICIO" => "ordenes_unidades",
    *  "VENTAS DE POST VENTA" => "ventas_post_venta",
    *  "TOTAL DE GASTOS OPERATIVOS" => "datos_generales",
    *  "COSTO FINANCIERO CONSOLIDADO" => "costos_financieros_prestamos",
    *  "BONOS MARCA" => "complementos",
    *  "UNO" => "datos_generales",
    *  "ACUMULADO PERSONAL CONSOLIDADO" => "datos_generales",
    *  "UTILIDAD POR AREA" => "utilidad_area",
    *  ];
    *  // Relación campo->campo tabla

    * 
    *  $relCampos = [
    *  "Nuevos" => "nuevos",
    *  "UB Nuevos" => "utilidad_nuevos",
    *  "Flotillas" => "flotillas",
    *   "UB Flotillas" => "utilidad_flotillas",
    *   "Seminuevos" => "seminuevos",
    *   "UB Seminuevos" => "utilidad_seminuevos",
    *   "Ordenes de servicios" => "servicio",
    *   "UB O. servicios" => "utilidad_servicio",
    *   "Ordenes de HyP" => "hyp",
    *   "UB Ordenes de HyP" => "utilidad_hyp",
    *   "Ventas Servicio" => "ventas_servicio",
    *   "Total Ventas Refacciones" => "total_ventas_ref",
    *   "Refacciones Servicio" => "refacciones_servicio",
    *   "Refacciones HyP" => "refacciones_hyp",
    *   "Refacciones Mostrador" => "refacciones_mostrador",
    *   "Total de Gastos Operativos" => "gasto",
    *   "CNuevos" => "nuevos",
    *   "CFlotillas" => "utilidad_nuevos",
    *   "Refacciones" => "refacciones",
    *   "Bajio" => "bajio",
    *   "Intercias" => "intercias",
    *   "Bonos Marca" => "bonos",
    *   "UNO" => "uno",
    *   "Personal" => "personal",
    *   "Area Comercial" => "area_comercial",
    *   "Area Postventa" => "area_postventa",
    *   ];
    *  //Relacion agencia->id_agencia
    *   $relAgencias = [
    *   "Azcapotzalco" => 26,
    *    "Ecatepec" => 27,
    *    "Vallejo" => 28,
    *   "Pachuca" => 29,
    *   ];
    */
}
