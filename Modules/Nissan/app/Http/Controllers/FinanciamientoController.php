<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use DateTime;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Modules\Nissan\Http\Requests\StoreFinanciamientoRequest;
use Modules\Nissan\Models\ComFinanciamiento;
use Modules\Nissan\Models\DatosVenta;
use Modules\Nissan\Models\Gasto;
use Modules\Nissan\Models\GastosVenta;
use Modules\Nissan\Services\ComisionesService;
use Modules\Nissan\Transformers\ComFinanciamientosResource;

class FinanciamientoController extends Controller
{

    protected $comService;
    public function __construct(ComisionesService $comService)
    {
        $this->comService = $comService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = ComFinanciamiento::active()->with('vendedor')->get();

        return response()->json(
            [
            'status' =>  'success',
            'data' => ComFinanciamientosResource::collection($query),
            'message' => 'datos recuperados correctamente',
            ], );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('nissan::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFinanciamientoRequest $request)
    {
        $guardados = [];
        $omitidos = [];

        foreach ($request->financiamientos as $i => $datos) {
            $resultado = $this->guardarFinanciamiento($datos, $request->file("financiamientos.$i.archivo"));

            if ($resultado['status'] === 'saved') {
            $guardados[] = $resultado['data'];
            }

            if ($resultado['status'] === 'ignored') {
                $omitidos[] = $resultado['message'];
            }
            }

        return response()->json([
            'status'  => 'success',
            'message' => count($guardados) . ' financiamiento(s) guardado(s) '. count($omitidos). ' omitido(s) por duplicidad' ,
            'data'    => [...$guardados, ...$omitidos]
        ], 201);
    }

    private function guardarFinanciamiento(array $datos, $archivo = null)
    {
        $comision = !empty($datos['id']) ? ComFinanciamiento::findOrFail($datos['id']) : new ComFinanciamiento();

        $isNoSerie   = strlen($datos['numero_factura']) === 17;
        $datosVenta  = !$isNoSerie
            ? $this->comService->getVentaByParam('no_factura', $datos['numero_factura'])
            : $this->comService->getVentaByParam('serie',      $datos['numero_factura']);

            // Evitar asignar dos veces una comision por financiamiento a una mis venta
        if (empty($datos['id']) && $datosVenta) {
            $ventaAsignada = ComFinanciamiento::where('com_datos_venta_id',$datosVenta->id)->first();
                if ($ventaAsignada) {
                    return [
                        'status' => 'ignored',
                        'message' => "La venta {$datos['numero_factura']} ya tiene una comisión asignada"
                    ];
                }
        }

        $comision->agencia                = $datos['agencia']                ?? null;
        $comision->com_vendedores_id      = $datos['com_vendedores_id']      ?? null;
        $comision->no_contrato            = $datos['no_contrato'];
        $comision->fecha_desembolso       = $datos['fecha_desembolso'];
        $comision->numero_factura         = !$isNoSerie ? $datos['numero_factura'] : ($datosVenta->no_factura ?? $datos['numero_factura']);
        $comision->monto_financiar        = $datos['monto_financiar'];
        $comision->incentivo_dealer       = $datos['incentivo_dealer'];
        $comision->porcentaje_asesor      = (($datos['porcentaje_asesor'] ?? 0) / 100);
        $comision->comision_asesor_pesos  = $datos['comision_asesor_pesos']  ?? null;
        $comision->tipo_financiamiento    = $datos['tipo_financiamiento']    ?? null;
        $comision->com_datos_venta_id     = $datosVenta->id                  ?? null;
        $comision->observaciones          = $datos['observaciones']          ?? null;
        $comision->kit_seguridad          = $datos['kit_seguridad']          ?? 0;
        $comision->sat_finder             = $datos['sat_finder']             ?? 0;
        $comision->garantia_extendida     = $datos['garantia_extendida']     ?? 0;
        $comision->seguro_vf3             = $datos['seguro_vf3']             ?? 0;
        $comision->accesorios_adicionales = $datos['accesorios_adicionales'] ?? 0;
        $comision->comision_mantenimiento = $datos['comision_mantenimiento'] ?? 0;
        $comision->comision_garantia_ext  = $datos['comision_garantia_extendida'] ?? 0;
        $comision->comision_udi           = $datos['comision_udi']           ?? 0;
        $comision->comision_vf3           = $datos['comision_vf3']           ?? 0;
        $comision->sub_x_des              = $datos['sub_x_des']              ?? 0;

        if ($archivo) {
            if ($comision->ruta_archivo && Storage::disk('public')->exists($comision->ruta_archivo)) {
                Storage::disk('public')->delete($comision->ruta_archivo);
            }
            $ext   = $archivo->getClientOriginalExtension();
            $nombre = $comision->no_contrato . '_' . $comision->numero_factura . '.' . $ext;
            $comision->ruta_archivo = $archivo->storeAs('comisiones/financiamientos', $nombre, 'public');
        }

        $comision->save();

        if(!empty($comision->com_datos_venta_id)){
           $gasto = GastosVenta::where('id_datos_venta',$comision->com_datos_venta_id)->first();
           if($gasto){
               $gasto->total_subsidios = $comision->sub_x_des;
               $gasto->save();
           }else{
            $gasto = new GastosVenta();
            $gasto->total_subsidios = $comision->sub_x_des;
            $gasto->id_datos_venta = $comision->com_datos_venta_id;
             $gasto->save();
           }
        }

        return [
            'status' => 'saved',
            'message' => 'Se asigno una comision la venta con factura'.  $comision->numero_factura
        ];
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('nissan::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $datosVenta = ComFinanciamiento::find($id);
        if($datosVenta){
            $estatusActual = (int) $datosVenta->estatus;
            $datosVenta->estatus = $this->comService->devolverEst($estatusActual);

            $datosVenta->comentario = $data['comentario'];
            $datosVenta->save();
        }

        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => 'EL estatus ha cambiado a '. $this->comService->getLabelStatus($datosVenta->estatus)
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $registro = ComFinanciamiento::find($id);
        if($registro){
            $registro->activo = 0;
            $registro->save();
           
            return response()->json([
            'status' => 'success',
            'message' => 'Registro eliminado correctamente',
            'data' => []
        ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'No se encontró el registro que desea eliminar',
            'data' => []
        ]);
        
    }

    public function getDataVenta($noFactura){
        $isNoSerie = strlen($noFactura) === 17 ;
        $query =  !$isNoSerie ? 
            $this->comService->getVentaByParam('no_factura', $noFactura) :
            $this->comService->getVentaByParam('serie', $noFactura);
        // $query = $this->comService->getVentaByParam('no_factura', $noFactura);

        return response()->json([
            'status' =>  'success',
            'data' => $query,
            'message' => 'datos recuperados correctamente',
        ], );
    }

    /**
     * Petición de consulta de datos mediante filtro
     * @param mixed $estatus estatus de busqueda (1-5)
     * @param mixed $agencia intercompania de agencia buscada
     * @param mixed $fechaInicio fehcha incial de busqueda
     * @param mixed $fechaFin fecha final de búsqueda
     * @param mixed $vendedor id de vendedor
     */
    public function queryFinanciamientos($estatus, $agencia, $vendedor, $fechaInicio, $fechaFin ){
        $agencia = $this->comService->parseAgencia($agencia);
        $fechaInicio =  $fechaInicio ? new DateTime($fechaInicio . " 00:00:00") : null;
        $fechaFin = $fechaFin ? new DateTime($fechaFin . " 23:59:59"): null;

        $financiamientos = ComFinanciamiento::
            with(['vendedor', 'venta'])
            ->when($agencia, fn($q) => $q->where('agencia', $agencia))
            ->when($vendedor, fn($q) => $q->where('com_vendedores_id', $vendedor))
            ->when($estatus, fn($q) => $q->where('estatus', $estatus))
            // ->when($tipoVenta, fn($q) => $q->where('clave_producto', $tipoVenta))
            ->when($fechaInicio && $fechaFin , fn($q) => $q->whereBetween('created_at', [$fechaInicio, $fechaFin]))
            ->active()
            ->get();

        return $financiamientos;
    }

     /**
     * Petición de consulta de datos mediante filtro
     * @param mixed $estatus estatus de busqueda (1-5)
     * @param mixed $agencia intercompania de agencia buscada
     * @param mixed $tipoVenta tipo de venta (nu-semi)
     * @param mixed $fechaInicio fehcha incial de busqueda
     * @param mixed $fechaFin fecha final de búsqueda
     * @param mixed $vendedor id de vendedor
     */
    public function getFinanciaminetos($estatus = null, $agencia =null, $fechaInicio  = null , $fechaFin  = null, $vendedor = null ){
    

    $data = $this->queryFinanciamientos(
                                        $estatus        == '12345' ? null : $estatus,
                                        $agencia        == 'todos' ? null : $agencia,
                                        $vendedor       == 'todos' ? null : $vendedor,
                                        $fechaInicio    == 'todos' ? null : $fechaInicio,
                                        $fechaFin       == 'todos' ? null : $fechaFin
                                    );
    return response()->json([
        'status' => 'success',
        'message' => 'Datos recuperados correctamente',
        'data' => ComFinanciamientosResource::collection($data),
        'estado' => $estatus
    ]);

    }

    public function avanzarEstatus($id)
    {
        $datosVenta = ComFinanciamiento::find($id);
        if($datosVenta){
            $estatusActual = (int) $datosVenta->estatus;
            $datosVenta->estatus = $this->comService->avanzarEst($estatusActual);
            $datosVenta->comentario = null;
            $datosVenta->save();
        }

        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => 'EL estatus ha cambiado a '. $this->comService->getLabelStatus($datosVenta->estatus)
        ]);
    }

    public function mostrarArchivo($id)
    {
        $comision = ComFinanciamiento::findOrFail($id);
        if (!$comision->ruta_archivo || !Storage::disk('public')->exists($comision->ruta_archivo)) {
            return response()->json(['error' => 'Archivo no encontrado'], 404);
        }
        return response()->file(storage_path('app/public/' . $comision->ruta_archivo));
    }


}
