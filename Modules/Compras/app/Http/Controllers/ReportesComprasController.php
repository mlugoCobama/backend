<?php

namespace Modules\Compras\Http\Controllers;

use App\Enums\EstatusSolicitud;
use App\Exports\GastosDetalleEmpresaExport;
use App\Exports\GastosMultiHojaExport;
use App\Exports\GatosDetalleMultiHojaExport;
use App\Exports\ReporteConcentradoComprasMultihojaExport;
use App\Exports\ReporteUnidadeMultihojaMacroExport;
use App\Exports\SolicitudesExport;
use App\Exports\WorkflowExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Services\ReportesService;
use Modules\Compras\Services\WorkFlowReporService;
use Modules\Compras\Transformers\GastosMensualesConcentradoResource;
use Modules\Compras\Transformers\GastosMensualesDetalleResource;

class ReportesComprasController extends Controller
{

    protected $reportesService;
    protected $workFlowService;
    public function __construct(
        ReportesService $reportesService,
        WorkFlowReporService $workFlowService
    ) {
        $this->reportesService = $reportesService;
        $this->workFlowService = $workFlowService;
    }

    /**
     * Recupera el detalle general de gastos por empresa
     * @param mixed $intercompania numero de intercompania de la empresa
     * @param mixed $fechaInicial fecha inicial del periodo a consultar
     * @param mixed $fechaFinal fecha inicial del periodo a consultar
     * @param mixed $tipo tipo de solicitud (1, 2, 3)
     */
    public function getGastoEmpresaDetalle($intercompania, $fechaInicial, $fechaFinal, $tipo){

        $data = $this->queryGastoEmpresaDetalle($intercompania, $fechaInicial, $fechaFinal, $tipo);
        $hasDatos = count($data) > 0 ? true : false; 

        return response()->json([
            'status' => 'success',
            'data' => GastosMensualesDetalleResource::collection($data),
            'hasDatos' => $hasDatos, 
            'message' => 'datos recuperados correctamente'
        ]);
    }

    /**
     * Recupera el concentrado de gastos
     * @param mixed $fechaInicial fecha inicial del periodo a consultar
     * @param mixed $fechaFinal fecha inicial del periodo a consultar
     * @param mixed $tipo tipo de solicitud (1, 2, 3)
     */
    public function getGastoEmpresasConcentrado($fechaInicial, $fechaFinal, $tipo){
        
        $data = $this->queryGastoEmpresasConcentrador($fechaInicial, $fechaFinal, $tipo);
        $hasDatos = count($data) > 0 ? true : false; 

        return response()->json([
            'status' => 'success',
            'data' => GastosMensualesConcentradoResource::collection($data),
            'hasDatos' => $hasDatos, 
            'message' => 'datos recuperados correctamente'
        ]);

    }

    /**
     * Descarga un archivo Excel con el concentrado de gastos por empresa.
     *
     *  $request - Debe de contener - fechaInicial, fechaFinal, tipo
     */
    public function descargarConcentrado(Request $request)
    {
        $fechaInicial = $request->fechaInicial;
        $fechaFinal = $request->fechaFinal;
        $tipo = $request->tipo;

        $concentrado =  $this->queryGastoEmpresasConcentrador($fechaInicial, $fechaFinal, $tipo);

        $detalleData = [];
        foreach ($concentrado as $empresa) {
            $detalleData[$empresa->empresa] = $this->reportesService->queryDetallesSolicitudes($empresa->num_intercompania, $fechaInicial, $fechaFinal, $tipo);
        }

        $fechaDescarga = now()->format('Y-m-d_His');
        $nombreArchivo = "GastoEmpresasConcentrado_{$fechaInicial}_{$fechaFinal}_{$tipo}_{$fechaDescarga}.xlsx";

        return Excel::download(
            new GastosMultiHojaExport($concentrado, $detalleData),
            $nombreArchivo
        );
    }

    /**
     * Descarga un archivo Excel con el detalle de gastos de una empresa específica.
     *
     * $request - Debe de contener - fechaInicial, fechaFinal, tipo
     * @param mixed $intercompania ID de la empresa intercompañía
     */
    public function descargarDetalleEmpresa(Request $request, $intercompania)
    {
        $fechaInicial = $request->fechaInicial;
        $fechaFinal = $request->fechaFinal;
        $tipo = $request->tipo;

        $concentrado = $this->queryGastoEmpresaDetalle($intercompania, $fechaInicial, $fechaFinal, $tipo);
        $detalle = $this->reportesService->queryDetallesSolicitudes($intercompania, $fechaInicial, $fechaFinal, $tipo);
        
        if (count($concentrado) === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'No hay datos para esta empresa'
            ]);
        }

        $empresaResult = DB::connection('intranet')->select('SELECT name FROM glpi_entities WHERE intercompania =' . $intercompania);
        $empresaNombre = $empresaResult[0]->name ?? 'Desconocida';

        $fechaDescarga = now()->format('Y-m-d_His');
        $nombreArchivo = "Gasto_{$empresaNombre}_{$fechaInicial}_{$fechaFinal}_{$tipo}_{$fechaDescarga}.xlsx";

        return Excel::download(
            new GatosDetalleMultiHojaExport($concentrado, $detalle, $empresaNombre),
            $nombreArchivo
        );
    }

    /**
     * Descarga un archivo Excel con las solicitudes de compra filtradas por tipo y estatus.
     *
     * @param mixed $tipo Tipo de solicitud (1 = generales, 2 = macro, 3 = RT)
     * @param mixed $estatus Estado de la solicitud
     */
    public function downloadSolicitudes( $tipo, $estatus, $fechaInicial, $fechaFinal )
    {
        $tipos = [
            1 => 'compras_grales',
            2 => 'compras_macro',
            3 => 'compras_rt',
        ];

        $hoy = date('d_m_Y');
        if ($estatus === 'all') {
        $metodos = [
            1 => 'descargarConcentradoGeneralesGlobal',
            2 => 'descargarConcentradoMacroGlobal',
            3 => 'descargarConcentradoGeneralesTiGlobal',
        ];

            return $this->{$metodos[$tipo]}();
        }

    $filename = "SC_{$hoy}_{$estatus}_{$tipos[$tipo]}.xlsx";
    return $this->genReportSolicitudesByStatus(
        $tipo, $estatus, $fechaInicial, $fechaFinal, $filename);
    }

    public function genReportSolicitudesByStatus( $tipo, $estatus, $fechaInicial, $fechaFinal, $filename){
        $solicitudes = $this->reportesService->querySolicitudesByStatus( $tipo, $estatus, $fechaInicial, $fechaFinal);
        return Excel::download( new SolicitudesExport($solicitudes), $filename,
            null, ['Content-Disposition' => 'attachment; filename="'.$filename.'"']
        );
    }


    //* Consultas

    /**
     * Ejecuta el procedimiento almacenado para obtener el detalle de gastos por empresa.
     *
     * @param mixed $intercompania ID de la empresa intercompañía
     * @param mixed $fechaInicial Fecha de inicio (formato YYYY-MM-DD)
     * @param mixed $fechaFinal Fecha de fin (formato YYYY-MM-DD)
     * @param mixed $tipo Tipo de gasto
     */
    private function queryGastoEmpresaDetalle($intercompania, $fechaInicial, $fechaFinal, $tipo){
        return DB::select('CALL SP_GastoPorEmpresaDetalle(?, ?, ?, ?)', [ $intercompania, $fechaInicial, $fechaFinal, $tipo]);
    }

    /**
     * Ejecuta el procedimiento almacenado para obtener el concentrado de gastos por empresa.
     *
     * @param mixed $fechaInicial Fecha de inicio (formato YYYY-MM-DD)
     * @param mixed $fechaFinal Fecha de fin (formato YYYY-MM-DD)
     * @param mixed $tipo Tipo de gasto
     */
    private function queryGastoEmpresasConcentrador($fechaInicial, $fechaFinal, $tipo){
        return DB::select('CALL SP_GastosPorEmpresaConcentrado(?, ?, ?)', [ $fechaInicial, $fechaFinal, $tipo ]);
    }

    public function descargarConcentradoGeneralesGlobal()
    {
        $concentrado = [
            333, 201, 131, 130, 251,210, 155, 135, 110, 111, 250, 240,
            132, 200, 119, 190, 133, 353, 191, 354, 353111, 251250,
        ];

        $detalleData = [];
        foreach ($concentrado as $empresa) {
            $detalleData[$empresa] = $this->reportesService->queryComprasGnerales($empresa, 1);
        }

        $fechaDescarga = now()->format('Y-m-d_His');
        $nombreArchivo = "GastoGeneralConcentrado_{$fechaDescarga}.xlsx";

        return Excel::download(
            new ReporteConcentradoComprasMultihojaExport($detalleData),
            $nombreArchivo
        );
    }

    public function descargarConcentradoGeneralesTiGlobal()
    {
        $concentrado = [
            333, 201, 131, 130, 251, 210, 155, 135, 110, 111, 250,
            240, 132, 200, 119, 190, 133, 353, 191, 354, 353111, 
            251250, 710,7051, 712, 700, 2000, 7064, 7062, 7063, 7061,
        ];

        $detalleData = [];
        foreach ($concentrado as $empresa) {
            $detalleData[$empresa] = $this->reportesService->queryComprasGnerales($empresa, 3);
        }

        $fechaDescarga = now()->format('Y-m-d_His');
        $nombreArchivo = "GastoGeneralTIConcentrado_{$fechaDescarga}.xlsx";

        return Excel::download(
            new ReporteConcentradoComprasMultihojaExport($detalleData),
            $nombreArchivo
        );
    }

    public function descargarConcentradoMacroGlobal()
    {
        $concentrado = [
            131, 130, 251, 210, 155, 135, 110, 111, 240, 250, 132,
            119, 190, 133, 353, 191, 354 , 251250, 353111
        ];

        $detalleData = [];
        foreach ($concentrado as $empresa) {
            $detalleData[$empresa] = $this->reportesService->queryComprasMacro($empresa);
        }

        $fechaDescarga = now()->format('Y-m-d_His');
        $nombreArchivo = "GastoMacroConcentrado_{$fechaDescarga}.xlsx";

        return Excel::download(
            new ReporteUnidadeMultihojaMacroExport($detalleData),
            $nombreArchivo
        );
    }

    public function getReporteEstatus($tipo){

        $nombreTipo = match ($tipo) {
            1 => 'ComprasGenerales',
            2 => 'ComprasMacrotaller',
            3 => 'ComprasRecursosTecnologicos',
            '1' => 'ComprasGenerales',
            '2' => 'ComprasMacrotaller',
            '3' => 'ComprasRecursosTecnologicos',
            default => $tipo,
        };

         $data = $this->workFlowService->getLogsEventos($tipo);

        return Excel::download(new WorkflowExport($data), 'reporte_tiempos_flujo_'.$nombreTipo.'.xlsx');
        // $data = $this->workFlowService->getLogsEventos();
        // return response()->json(
        //     [
        //         'data' => $data
        //     ]
        // );
    }

}
