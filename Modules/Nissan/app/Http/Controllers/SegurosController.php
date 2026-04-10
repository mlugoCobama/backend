<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Nissan\Http\Requests\StoreSeguroRequest;
use Modules\Nissan\Models\ComSeguro;
use Modules\Nissan\Models\DatosVenta;
use Modules\Nissan\Services\ComisionesService;
use Modules\Nissan\Transformers\SeguroResource;

class SegurosController extends Controller
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
        $query = ComSeguro::active()->with('vendedor')->get();

        return response()->json(
            [
            'status' =>  'success',
            'data' => SeguroResource::collection($query),
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
    public function store(StoreSeguroRequest $request)
    {

    $resultados = [];

        foreach ($request->seguros as $i => $datos) {
            $resultados[] = $this->guardarSeguro($datos, $request->file("financiamientos.$i.archivo"));
        }

        return response()->json([
            'status'  => 'success',
            'message' => count($resultados) . ' financiamiento(s) guardado(s) correctamente',
            'data'    => $resultados
        ], 201);
    }

    private function guardarSeguro($data){
                $comision = !empty($datos['id'])
            ? ComSeguro::findOrFail($datos['id'])
            : new ComSeguro();

        // Relaciones
        $comision->com_vendedores_id = $data['com_vendedores_id'];
        $comision->agencia = $data['agencia'];

        // Básicos
        $comision->folio = $data['folio'];
        $comision->poliza = $data['poliza'];
        $comision->aseguradora = $data['aseguradora'];
        $comision->nombre = $data['nombre'];
        $comision->unidad = $data['unidad'];
        $comision->serie = $data['serie'];

        // Fechas
        $comision->fecha_emision = $data['fecha_emision'];

        // Info adicional
        $comision->forma_pago = $data['forma_pago'];

        // Montos
        $comision->prima_neta = $data['prima_neta'];

        $comision->vs = $this->calcularVS($data['prima_neta']);

        $comision->com_encargado_seg = $data['calcular_encargado_seg']
            ? $this->calcularEncargadoSeg($data['prima_neta'])
            : 0;

        $comision->comision_apv_pesos = $this->calcularComisionAPV($data['prima_neta']);

        // Extras
        $comision->observaciones = $data['observaciones'];

        // Guardar
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
        $datosVenta = ComSeguro::find($id);
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
      $registro = ComSeguro::find($id);
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
        $query = $this->comService->getVentaByParam('no_factura', $noFactura);

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
                // intercompanias => Azcapo   Campestre  Universidad    Agencia ingresada
        $agencia = $this->comService->parseAgencia($agencia);

        $financiamientos = ComSeguro::
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
        'data' => SeguroResource::collection($data),
        'estado' => $estatus
    ]);

    }

    public function avanzarEstatus($id)
    {
        $datosVenta = ComSeguro::find($id);
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


    private function calcularComisionAPV($prima)
        {
            return ($prima ?? 0) * 0.04;
        }

        private function calcularVS($prima)
        {
            return ($prima ?? 0) * 0.2; 
        }

        private function calcularEncargadoSeg($prima)
        {
            return ($prima ?? 0) * 0.01; 
        }
}
