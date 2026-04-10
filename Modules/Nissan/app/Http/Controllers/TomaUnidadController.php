<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Modules\Nissan\Models\ComTomaUnidad;
use Modules\Nissan\Transformers\ComTomaUnidadesResource;
use Modules\Nissan\Http\Requests\StoreTomaUnidadRequest;
use Modules\Nissan\Models\DatosVenta;
use Modules\Nissan\Services\ComisionesService;

class TomaUnidadController extends Controller
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
        $query = ComTomaUnidad::active()->with('vendedor')->get();

        return response()->json(
            [
            'status' =>  'success',
            'data' => ComTomaUnidadesResource::collection($query),
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
    public function store(StoreTomaUnidadRequest $request)
    {
        $resultados = [];

        foreach ($request->toma_unidad as $i => $datos) {
            $resultados[] = $this->guardarTomaUnidad($datos);
        }

        return response()->json([
            'status'  => 'success',
            'message' => count($resultados) . ' toma de unidad(es) guardada(s) correctamente',
            'data'    => $resultados
        ], 201);
    }


    public function guardarTomaUnidad(array $datos){
        $comision = !empty($datos['id']) ? ComTomaUnidad::findOrFail($datos['id']) : new ComTomaUnidad();
        
        $datosVenta =  $this->comService->getVentaByParam('serie', $datos['numero_serie']);

        $comision->com_vendedores_id   = $datos['com_vendedores_id'] ?? null;
        $comision->agencia             = $datos['agencia'] ?? null;

        $comision->no_inventario       = $datos['por_inventario'] ?? null;
        $comision->vehiculo            = $datos['vehiculo'] ?? null;
        $comision->no_serie            = $datos['numero_serie'] ?? null;
        $comision->comision_apv_pesos  = $datos['comision_apv_pesos'] ?? null;
        $comision->fecha_toma          = $datos['fecha_toma'] ?? null;
        $comision->observaciones       = $datos['observaciones'] ?? null;
        $comision->tipo_apv            = $datos['tipo_apv'] ?? null;
        
        $comision->id_com_datos_venta  = $datosVenta ? $datosVenta->id : null;

        $comision->save();
        return $comision;
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
        return view('nissan::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $datosVenta = ComTomaUnidad::find($id);
        if($datosVenta){
            $estatusActual = (int) $datosVenta->estatus;
            $datosVenta->estatus = $this->comService->devolverEst($estatusActual);

            $datosVenta->comentario = $data['comentario'];
            $datosVenta->save();
        }

        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => 'El estatus ha cambiado a '. $this->comService->getLabelStatus($datosVenta->estatus)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $registro = ComTomaUnidad::find($id);
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
        
        $partes = explode('-', str_replace(' ', '-', $noFactura));
        if(count($partes)){
            $claveProducto = $partes[0]; // NU
            $anio = $partes[1]; // 2026
            $noInventario = $partes[2]; // 123

            if (strlen($anio) == 2) {
                $anio = str_pad($anio, 4, '20', STR_PAD_LEFT);
            }
            
            $query = DatosVenta::where('clave_producto', strtoupper($claveProducto))
                                ->where('anio_vehiculo', $anio)
                                ->where('no_inventario', strtoupper($noInventario))
                                ->first();

            return response()->json([
                'status' =>  'success',
                'data' => $query,
                'message' => 'datos recuperados correctamente',
            ], );
        }

         return response()->json([
                'status' =>  'success',
                'data' => [],
                'message' => 'Formato incorrecto de inventario',
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
                // intercompanias => Azcapo   Campestre  Universidad    Agencia ingresada
        $agencia = $this->comService->parseAgencia($agencia); 

        $financiamientos = ComTomaUnidad::
            with(['vendedor'])
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
    public function getTomaUnidad($estatus = null, $agencia =null, $tipoVenta = null, $fechaInicio  = null , $fechaFin  = null, $vendedor = null ){
    

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
        'data' => ComTomaUnidadesResource::collection($data),
        'estado' => $estatus
    ]);

    }

    public function avanzarEstatus($id)
    {
        $datosVenta = ComTomaUnidad::find($id);
        if($datosVenta){
            $estatusActual = (int) $datosVenta->estatus;
            $datosVenta->estatus = $this->comService->avanzarEst($estatusActual);
            $datosVenta->comentario = null;
            $datosVenta->save();
        }
        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => 'El estatus ha cambiado a '. $this->comService->getLabelStatus($datosVenta->estatus)
        ]);
    }

}
