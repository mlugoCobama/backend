<?php

namespace Modules\Compras\Http\Controllers;

use App\Enums\EstatusSolicitud;
use App\Exports\GastosDetalleEmpresaExport;
use App\Exports\GastosMultiHojaExport;
use App\Exports\GatosDetalleMultiHojaExport;
use App\Exports\SolicitudesExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Transformers\GastosMensualesConcentradoResource;
use Modules\Compras\Transformers\GastosMensualesDetalleResource;

class ReportesComprasController extends Controller
{
    public function index()
    {
        return view('compras::index');
    }

    public function create()
    {
        return view('compras::create');
    }

    public function store(Request $request): RedirectResponse
    {
    }

    public function show($id)
    {
        return view('compras::show');
    }

    public function edit($id)
    {
        return view('compras::edit');
    }

    public function update(Request $request, $id): RedirectResponse
    {
    }

    public function destroy($id)
    {
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
            $detalleData[$empresa->empresa] = $this->queryDetallesSolicitudes($empresa->num_intercompania, $fechaInicial, $fechaFinal, $tipo);
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
        $detalle = $this->queryDetallesSolicitudes($intercompania, $fechaInicial, $fechaFinal, $tipo);
        
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

        $solicitudes = $this->querySolicitudesByStatus( $tipo, $estatus, $fechaInicial, $fechaFinal);

        $filename = 'SC_'.$hoy.'_'.$estatus.'_'.$tipos[$tipo].'.xlsx';
        return Excel::download(
            new SolicitudesExport($solicitudes),
            $filename,
            null,
            ['Content-Disposition' => 'attachment; filename="'.$filename.'"']
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

    /**
     * Consulta las solicitudes de compra con sus detalles y los transforma en formato exportable.
     *
     * @param mixed $estatus Estado de la solicitud
     * @param mixed $tipo Tipo de solicitud
     */
    private function queryDetallesSolicitudes( $empresa, $fechaInicio, $fechaFin, $tipo ){
        $empresas = [
            333 => 'CORPORACION ADMINISTRATIVA DEL SUR', 201 => 'AGRUPAMIENTO',
            131 => 'AZTECA GAS', 130 => 'SATELITE GAS', 251 => 'FLAMAMEX',
            210 => 'REYES GAS', 155 => 'GASAMEX', 135 => 'SEGAS', 110 => 'GARZA GAS',
            111 => 'GARZA SUR', 250 => 'GAS FLAMAZUL', 132 => 'GAS PREMIO',
            200 => 'TANQUES SONI', 119 => 'TANQUES GARZA GAS', 190 => 'ZUGAS',
            133 => 'GASERA MULTIREGIONAL', 353 => 'GAS URBANO', 710 => 'NISSAN UNIVERSIDAD',
            7051 => 'NISSAN AZCAPOTZALCO', 712 => 'NISSAN CAMPESTRE', 700 => 'CORPORATIVO AUTOS SONI',
            240 => 'SERVIGAS DEL VALLE', 2000 => 'SERVICIO EL ONCE', 7064 => 'RENAULT AZCAPOTZALCO',
            7062 => 'RENAULT ECATEPEC', 7063 => 'RENAULT VALLEJO', 7061 => 'RENAULT PACHUCA',
            191 => 'BARAGAS', 354 => 'IZTAGAS Y ENERGIA',
        ];


    $solicitudes = SolicitudesCompra::with([
        'DestinoVehiculo',
        'Cotizaciones.orden_compra',
        'DetallesSolicitud.DetalleAutotanque.DatosVehiculo',
        'DetallesSolicitud.unidadMedida',
        'DetallesSolicitud.DetallesCotizacion.CotizacionesProveedores.datos_proveedor'
    ])
    ->where('estatus','>', 7)
    ->where('activo', 1)
    ->where('tipo', $tipo)
    ->where('empresa', $empresa)
    ->whereBetween('fecha', [$fechaInicio, $fechaFin])
    ->get()
    ->flatMap(function ($solicitud) use ($empresas) {


        //   OBTENER LA COTIZACIÓN QUE TIENE UNA ORDEN DE COMPRA
        $cotizacionOC = $solicitud->cotizaciones->firstWhere('orden_compra', '!=', null);
        $folioOC = $cotizacionOC->orden_compra->folio_oc ?? '';

        $labels = EstatusSolicitud::labels();
        $label = $labels[$solicitud->estatus] ?? 'DESCONOCIDO';

        $rows = [];
        $subtotal = 0;


        //   CALCULAR SUBTOTAL
        foreach ($solicitud->DetallesSolicitud as $detalle) {

            $cotSel = $detalle->DetallesCotizacion
                ->firstWhere(fn($cot) =>
                    $cot->CotizacionesProveedores &&
                    $cot->CotizacionesProveedores->seleccionado == 1
                );

            $precio = (float) ($cotSel->importe_unitario ?? 0);
            $cantidad = $detalle->cantidad ?? 1;

            $subtotal += $precio * $cantidad;
        }

        $iva = $subtotal * 0.16;
        $total = $subtotal + $iva;
        $proveedor = $cotSel->CotizacionesProveedores->datos_proveedor->nombre ?? 'N/A';

        $destinoSolicitud = $solicitud->DestinoVehiculo->nro_economico ?? null;
        $destinoFormat = ($solicitud->tipo == 2 && $destinoSolicitud)
            ? "ECO: $destinoSolicitud"
            : 'N/A';

        //   FILA PRINCIPAL: TOTALES DE LA SOLICITUD
        $rows[] = [
            'Folio'         => $solicitud->folio,
            'Folio_OC'      => $folioOC,
            'Fecha'         => date('d/m/Y H:i', strtotime($solicitud->fecha)),
            'Empresa'       => $empresas[$solicitud->empresa] ?? 'N/A',
            'Destino'       => $destinoFormat, 
            'Estado'        => $label,
            'Cantidad'      => '',
            'Descripcion'   => 'TOTALES DE LA SOLICITUD',
            'Observaciones' => '',
            'Unidad'        => '',
            'Proveedor'     => $proveedor,
            'Precio'        => '',
            'Subtotal'      => $subtotal,
            'IVA'           => $iva,
            'Total'         => $total,
        ];

        //   FILAS DETALLE DE LA SOLICITUD
        foreach ($solicitud->DetallesSolicitud as $detalle) {

            $cotSel = $detalle->DetallesCotizacion
                ->firstWhere(fn($cot) =>
                    $cot->CotizacionesProveedores &&
                    $cot->CotizacionesProveedores->seleccionado == 1
                );
  
            $precio = (float) ($cotSel->importe_unitario ?? 0);

            $ecoDetalle = $detalle->DetalleAutotanque->DatosVehiculo->nro_economico ?? null;
            $ecoDetalleFormat = ($solicitud->tipo == 2 && $ecoDetalle)
                ? "ECO: $ecoDetalle"
                : 'N/A';
            
            $rows[] = [
                'Folio'         => '',
                'Folio_OC'      => $folioOC,
                'Fecha'         => '',
                'Empresa'       => '',
                'Destino'       => $ecoDetalleFormat,
                'Estado'        => '',
                'Cantidad'      => $detalle->cantidad ?? 0,
                'Descripcion'   => $detalle->descripcion ?? '',
                'Observaciones' => $detalle->observaciones ?? '',
                'Unidad'        => $detalle->unidadMedida->nombre ?? '',
                'Proveedor'     => '',
                'Precio'        => $precio,

                'Subtotal'      => '',
                'IVA'           => '',
                'Total'         => '',
            ];
        }

        return $rows;
    });

        return $solicitudes;
    }


    public function querySolicitudesByStatus( $tipo, $estatus, $fechaInicial, $fechaFinal)
    {

        $empresas = [
            333 => 'CORPORACION ADMINISTRATIVA DEL SUR', 201 => 'AGRUPAMIENTO',
            131 => 'AZTECA GAS', 130 => 'SATELITE GAS', 251 => 'FLAMAMEX',
            210 => 'REYES GAS', 155 => 'GASAMEX', 135 => 'SEGAS', 110 => 'GARZA GAS',
            111 => 'GARZA SUR', 250 => 'GAS FLAMAZUL', 132 => 'GAS PREMIO',
            200 => 'TANQUES SONI', 119 => 'TANQUES GARZA GAS', 190 => 'ZUGAS',
            133 => 'GASERA MULTIREGIONAL', 353 => 'GAS URBANO', 710 => 'NISSAN UNIVERSIDAD',
            7051 => 'NISSAN AZCAPOTZALCO', 712 => 'NISSAN CAMPESTRE', 700 => 'CORPORATIVO AUTOS SONI',
            240 => 'SERVIGAS DEL VALLE', 2000 => 'SERVICIO EL ONCE', 7064 => 'RENAULT AZCAPOTZALCO',
            7062 => 'RENAULT ECATEPEC', 7063 => 'RENAULT VALLEJO', 7061 => 'RENAULT PACHUCA',
            191 => 'BARAGAS', 354 => 'IZTAGAS Y ENERGIA',
        ];

        $solicitudes = SolicitudesCompra::with('DetallesSolicitud.unidadMedida')
            ->where('estatus', $estatus)
            ->where('activo', 1)
            ->where('tipo', $tipo)
            ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
            ->get()
            ->flatMap(function ($solicitud) use ($empresas) {
                
                $detalles = $solicitud->DetallesSolicitud;
                return $detalles->map(function ($detalle, $index) use ($solicitud, $empresas) {
                    $labels = EstatusSolicitud::labels();
                    $label = $labels[$solicitud->estatus] ?? 'DESCONOCIDO';
                    return [
                        'Folio'        => $index === 0 ? $solicitud->folio : '',
                        'Fecha'        => $index === 0 ? date('d/m/Y H:i', strtotime($solicitud->fecha)) : '',
                        'Empresa'      => $index === 0 ? ($empresas[$solicitud->empresa] ?? 'N/A') : '',
                        'Estado'       => $index === 0 ? $label : '',
                        'Cantidad'     => $detalle->cantidad ?? 0,
                        'Unidad'       => $detalle->unidadMedida->nombre ?? '',
                        'Descripción'  => $detalle->descripcion ?? '',
                        'Observaciones'=> $detalle->observaciones ?? '',
                    ];
                });
            });
        
        return $solicitudes;
    }
}
