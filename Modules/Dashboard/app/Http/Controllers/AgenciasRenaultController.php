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
use App\Models\Inventarios;
use App\Models\UtilidadArea;
use App\Models\OrdenesUnidades;
use App\Models\Personal;

class AgenciasRenaultController extends Controller
{
    private array $meses = [
         1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
         5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
         9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
     ];

        private $relTablas = [
        "UNIDADES VENDIDAS" => "ordenes_unidades",
        "ORDENES DE SERVICIO" => "ordenes_unidades",
        "VENTAS DE POST VENTA" => "ventas_post_venta",
        "TOTAL DE GASTOS OPERATIVOS" => "datos_generales",
        "COSTO FINANCIERO CONSOLIDADO" => "costos_financieros_prestamos",
        "PRESTAMOS" => "costos_financieros_prestamos",
        "BONOS MARCA" => "complementos",
        "OBJETIVOS" => "complementos",
        "UNO" => "datos_generales",
        "ACUMULADO PERSONAL CONSOLIDADO" => "datos_generales",
        "PERSONAL POR AREA" => "personal",
        "UTILIDAD POR AREA" => "utilidad_area",
        "CONCEPTOS AREA COMERCIAL" => "utilidad_area",
        "CONCEPTOS AREA POSTVENTA" => "utilidad_area",
        "INVENTARIOS" => "inventarios",
        "ANTIGÜEDAD INVENTARIOS NUEVOS" => "inventarios",
        "ANTIGÜEDAD INVENTARIOS SEMI" => "inventarios",
    ];

    /**
     * Relación entre los campos del front y los de la bd
     */
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
        "Plan Piso" => "plan_piso",
        "Plan Piso Intereses" => "plan_piso_interes",
        "Nrf" => "nrf",
        "Nrf Intereses" => "nrf_interes",
        "Bonos Marca" => "bonos",
        "Objetivo" => "objetivo",
        "Cumplimiento" => "cumplimiento",
        "Porcentaje" => "porcentaje",
        "Servicio" => "servicio",
        "Servicios" => "servicios",
        "Apvs" => "apvs",
        "Hyp" => "hyp",
        "Inv Nuevo 101" => "inv_nuevo_101",
        "Inv Nuevo 201" => "inv_nuevo_201",
        "Inv Nuevo 301" => "inv_nuevo_301",
        "Inv Nuevo 401" => "inv_nuevo_401",
        "Inv Semi 101" => "inv_semi_101",
        "Inv Semi 201" => "inv_semi_201",
        "Inv Semi 301" => "inv_semi_301",
        "Inv Semi 401" => "inv_semi_401",
        'Ventas' => 'ventas',
        'Usados' => 'usados',
        'Admin' => 'admin'
    ];

    
    
        private $relAgencias = [
            "Azcapotzalco" => 26,
            "Ecatepec" => 27,
            "Vallejo" => 28,
            "Pachuca" => 29,
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

            // $periodoExiste = in_array($newPeriodo, $arr_mesesDatos);
            
            if($periodoExiste === false){
                $anio = $anio - 1;
            }

        }while( count($dataAnio) < 1 );

        if($periodoExiste === false){

            $fecha = end($arr_mesesDatos);

            $data = $this->conjuntoDatos($fecha, $sub_division,  $dataAnio);
            $fechaRecuperada = DateTime::createFromFormat('Y-m-d', $fecha);
            $mesRec = $fechaRecuperada->format('m');
            $anioRec = $fechaRecuperada->format('Y');
            $nombreMes = $this->meses[intval($mesRec)];

            return response()->json(
                [
                    'success' => true,
                    'message' => "No hay datos de este periodo en su lugar se muestran los de $nombreMes $anioRec",
                    'data' => $data
                ]);
                
        }else{
            $fecha = $periodoBuscado;
            
            $data = $this->conjuntoDatos($fecha , $sub_division,  $dataAnio);
            $nombreMes = intval($mes) == 0 ? $this->meses[12] : $this->meses[intval($mes)];
            return response()->json(
            [
                'success' => true,
                'message' => "Mostrando datos de $nombreMes $anio",
                'data' => $data,
            ]
        );
        }
    }

    private function conjuntoDatos($fechaBusqueda, $sub_division,  $dataAnio){

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
            $dataAnioAnt2 =  $this->getDataMesRenault($mes, ($anioAnt - 1));
            $totalAnio =  DataAnualAgenciasResource::collection($dataAnio);
            $totalAnioAnt = $this->getDataAnualAgencias($anioAnt, $sub_division);
            $antInventarios = $this->getDataAntInventarios($mes, $anio, $sub_division);    
                $data = [
                    'mes' => $dataMes,
                    'mesAnt' => $dataMesAnt,
                    'anioAnt' => $dataAnioAnt,
                    'totalAnio' => $totalAnio,
                    'totalAnioAnt' => $totalAnioAnt,
                    'totalAnioAnt2' => $dataAnioAnt2,
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
         return DataAnualAgenciasResource::collection(DB::connection('dashboard')->select("call Dashboard.SP_GetDataAnualAgencias($anio, $subDivision)"));
     }

     private function getDataAntInventarios(int $mes, int $anio, string $subDivision)
     {
         return DB::connection('dashboard')->select("call Dashboard.SP_GetDataAntSemestralInventarios($mes, $anio, $subDivision)");
     }

    
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
        $jsonData = $this->procesarArraytoJson($dataMesAgencias, $request, $fecha);

        // foreach ($jsonData as $agenciaId => $secciones) {
        //     foreach ($secciones as $seccion => $valores) {
        //         switch ($seccion) {
        //             case 'ordenes_unidades':
        //                 OrdenesUnidades::create(array_merge(['sucursales_id' => $agenciaId], $valores));
        //                 break;
        //             case 'ventas_post_venta':
        //                 VentasPostVenta::create(array_merge(['sucursales_id' => $agenciaId], $valores));
        //                 break;
        //             case 'datos_generales':
        //                 DatosGenerales::create(array_merge(['sucursales_id' => $agenciaId], $valores));
        //                 break;
        //             case 'costos_financieros_prestamos':
        //                 CostosFinancierosPrestamos::create(array_merge(['sucursales_id' => $agenciaId], $valores));
        //                 break;
        //             case 'complementos':
        //                 Complementos::create(array_merge(['sucursales_id' => $agenciaId], $valores));
        //                 break;
        //             case 'utilidad_area':
        //                 UtilidadArea::create(array_merge(['sucursales_id' => $agenciaId], $valores));
        //                 break;
        //             case 'personal':
        //                 Personal::create(array_merge(['sucursales_id' => $agenciaId], $valores));
        //             case 'inventarios':
        //                 Inventarios::create(array_merge(['sucursales_id' => $agenciaId], $valores));
        //                 break;
        //         }
        //     }
        // }

        foreach ($jsonData as $agenciaId => $secciones) {
            foreach ($secciones as $seccion => $valores) {
                $modelClass = match ($seccion) {
                    'ordenes_unidades' => OrdenesUnidades::class,
                    'ventas_post_venta' => VentasPostVenta::class,
                    'datos_generales' => DatosGenerales::class,
                    'costos_financieros_prestamos' => CostosFinancierosPrestamos::class,
                    'complementos' => Complementos::class,
                    'utilidad_area' => UtilidadArea::class,
                    'personal' => Personal::class,
                    'inventarios' => Inventarios::class,
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

       // Procesamiento del array a json
       $jsonData = $jsonData = $this->procesarArraytoJson($dataMesAgencias, $request, $fecha);
        
        foreach ($jsonData as $agenciaId => $secciones) {
            foreach ($secciones as $seccion => $valores) {
                $modelClass = match ($seccion) {
                    'ordenes_unidades' => OrdenesUnidades::class,
                    'ventas_post_venta' => VentasPostVenta::class,
                    'datos_generales' => DatosGenerales::class,
                    'costos_financieros_prestamos' => CostosFinancierosPrestamos::class,
                    'complementos' => Complementos::class,
                    'utilidad_area' => UtilidadArea::class,
                    'personal' => Personal::class,
                    'inventarios' => Inventarios::class,
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

    public function procesarArraytoJson($dataMesAgencias, $request, $fecha)
    {
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
                        $value = str_replace(',', '', trim($cell['value'] ?? ""));

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

        return $jsonData;
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
      *  Estructura para devolver el array
      */ 
    private $estructura = [
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
        "PRESTAMOS" => [
            ['value' => "plan_piso", 'colspan' => 1],
            ['value' => "plan_piso_interes", 'colspan' => 1],
            ['value' => "nrf", 'colspan' => 1],
            ['value' => "nrf_interes", 'colspan' => 1],
        ],
        "BONOS MARCAS" => [
            ['value' => "bonos", 'colspan' => 1],
        ],
        "OBJETIVOS" => [
            ['value' => "objetivo", 'colspan' => 1],
            ['value' => "cumplimiento", 'colspan' => 1],
            ['value' => "porcentaje", 'colspan' => 1],
        ],
        "UNO" => [
            ['value' => "uno", 'colspan' => 1],
        ],
        "ACUMULADO PERSONAL CONSOLIDADO" => [
            ['value' => "personal", 'colspan' => 1],
        ],
        "PERSONAL POR AREA" => [
            ['value' => "personal_ventas", 'colspan' => 1],
            ['value' => "personal_usados", 'colspan' => 1],
            ['value' => "personal_refacciones", 'colspan' => 1],
            ['value' => "personal_servicios", 'colspan' => 1],
            ['value' => "personal_admin", 'colspan' => 1],
            ['value' => "personal_apvs", 'colspan' => 1],

        ],
        "UTILIDAD POR AREA" => [
            ['value' => "area_comercial", 'colspan' => 1],
            ['value' => "area_postventa", 'colspan' => 1],
        ],
        "CONCEPTOS AREA COMERCIAL" => [
            ['value' => "area_nuevos", 'colspan' => 1],
            ['value' => "area_seminuevos", 'colspan' => 1],
            ['value' => "area_flotillas", 'colspan' => 1],
        ],
        "CONCEPTOS AREA POSTVENTA" => [
            ['value' => "area_servicio", 'colspan' => 1],
            ['value' => "area_refacciones", 'colspan' => 1],
            ['value' => "area_hyp", 'colspan' => 1],
        ],

        "INVENTARIOS" => [
            ['value' => "inventario_nuevos", 'colspan' => 1],
            ['value' => "inventario_seminuevos", 'colspan' => 1],
            ['value' => "inventario_refacciones", 'colspan' => 1],
        ],

        "ANTIGÜEDAD INVENTARIOS NUEVOS" => [
            ['value' => "inv_nuevo_101", 'colspan' => 1],
            ['value' => "inv_nuevo_201", 'colspan' => 1],
            ['value' => "inv_nuevo_301", 'colspan' => 1],
            ['value' => "inv_nuevo_401", 'colspan' => 1],
        ],

        "ANTIGÜEDAD INVENTARIOS SEMI" => [
            ['value' => "inv_semi_101", 'colspan' => 1],
            ['value' => "inv_semi_201", 'colspan' => 1],
            ['value' => "inv_semi_301", 'colspan' => 1],
            ['value' => "inv_semi_401", 'colspan' => 1],
        ],

    ];
            //Relación campos devueltos por la bd y 
            //nombres visuales de los campos
    private $mapaCampos = [
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
        "plan_piso" => "Plan Piso",
        "plan_piso_interes" => "Plan Piso Intereses",
        "nrf" => "Nrf",
        "nrf_interes" => "Nrf Intereses",
        "objetivo" => "Objetivo",
        "cumplimiento" => "Cumplimiento",
        "porcentaje" => "Porcentaje",
        "personal_ventas" => "Ventas",
        "personal_usados" => "Usados",
        "personal_refacciones" => "Refacciones",
        "personal_servicios" => "Servicios",
        "personal_admin" => "Admin",
        "personal_apvs" => "Apvs",
        "area_nuevos" => "Nuevos",
        "area_seminuevos" => "Seminuevos",
        "area_flotillas" => "Flotillas",
        "area_servicio" => "Servicio",
        "area_refacciones" => "Refacciones",
        // "area_refacciones" => "Refacciones",
        "area_hyp" => "Hyp",

        "inventario_nuevos" => "Nuevos",
        "inventario_seminuevos" => "Refacciones",
        "inventario_refacciones" => "Seminuevos",
        
        "inv_nuevo_101" => "Inv Nuevo 101",
        "inv_nuevo_201" => "Inv Nuevo 201",
        "inv_nuevo_301" => "Inv Nuevo 301",
        "inv_nuevo_401" => "Inv Semi 401",
        "inv_semi_101" => "Inv Semi 101",
        "inv_semi_201" => "Inv Semi 201",
        "inv_semi_301" => "Inv Semi 301",
        "inv_semi_401" => "Inv Semi 401",
    ];

    /**
     * Recupera los datos del Store Procedure
     * Formatea los datos a json
     * Genera una estructura para mostrar los datos en el fronted
     */
    public function getDataGridAgencia($mes, $anio)
    {
        $datos = DB::select('call Dashboard.SP_GetDataMesRenault(' . $mes . ',' . $anio . ')');
        // Convierte $datos a un arreglo
        $datos = json_decode(json_encode($datos), true);
        $tamanioDatos = count($datos);

        if (count($datos) > 1) {
            
            $resultado = [];

            foreach ($this->estructura as $seccion => $filas) {
                $resultado[] = [['value' => $seccion, 'colspan' => 6]];
                foreach ($filas as $fila) {
                    $nombreCampo = $this->mapaCampos[$fila['value']] ?? $fila['value'];
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
