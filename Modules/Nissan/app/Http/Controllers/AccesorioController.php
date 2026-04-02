<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Nissan\Models\ComAccesorios;
use Modules\Nissan\Models\ComDetalleAccesorio;
use Modules\Nissan\Models\DatosVenta;
use Modules\Nissan\Transformers\ComAccesoriosResource;

class AccesorioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = ComAccesorios::active()->with(['vendedor', 'detalles'])->get();

        return response()->json(
            [
            'status' =>  'success',
            'data' => ComAccesoriosResource::collection($query),
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
    public function store(Request $request)
    {
       $comision = $request->filled('id')  ? ComAccesorios::findOrFail($request->id) : new ComAccesorios();

       $comision->com_vendedores_id  = $request->com_vendedores_id;
       $comision->no_factura         = $request->no_factura;
       $comision->sub_total_factura  = $request->subtotal_factura;
       $comision->comision_apv_pesos = $request->comision_apv_pesos;
       $comision->agencia         = $request->agencia;
       $comision->observaciones      = $request->observaciones;

       $comision->save();
       $esNuevo = $comision->wasRecentlyCreated;

    if ($request->has('detalles')) {
        $detalles = json_decode($request->detalles);

        $idsExistentes = $comision->detalles()->pluck('id')->toArray();
        $idsEnviados   = collect($detalles)->pluck('id')->filter()->toArray();

        $aEliminar = array_diff($idsExistentes, $idsEnviados);
        ComDetalleAccesorio::destroy($aEliminar);

        foreach ($detalles as $detalle) {
            if (isset($detalle->id)) {
                $this->updateDetalle($detalle->id, $detalle, $comision->id);
                // $comision->detalles()->where('id', $detalle['id'])->update($detalle);
            } else {
                // $comision->detalles()->create($detalle);
                $this->createDetalle($detalle, $comision->id);
            }
        }
    }

    return response()->json([
        'status'  => 'success',
        'message' => $esNuevo ? 'Comisión creada correctamente' : 'Comisión actualizada correctamente',
        'data'    => []
    ], $esNuevo ? 201 : 200);
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
        $datosVenta = ComAccesorios::find($id);
        if($datosVenta){
            $estatusActual = (int) $datosVenta->estatus;
            $datosVenta->estatus = $estatusActual > 0 ? $estatusActual - 1 : $estatusActual;

            $datosVenta->comentario = $data['comentario'];
            $datosVenta->save();
        }

        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => 'La partida ha regresado al estado anterior exitosamente'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $registro = ComAccesorios::find($id);
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

    public function createDetalle($data, $idAccesorio){
    $detalle = new ComDetalleAccesorio();
    $detalle->concepto = $data->concepto;
    $detalle->cantidad = $data->cantidad;
    $detalle->importe = $data->importe;
    $detalle->com_accesorios_id = $idAccesorio;
    $detalle->save();

    }

    public function updateDetalle($id, $data, $idAccesorio){
        $detalle =  ComDetalleAccesorio::find($id);
        if($detalle){
            $detalle->concepto = $data->concepto;
            $detalle->cantidad = $data->cantidad;
            $detalle->importe = $data->importe;
            $detalle->com_accesorios_id = $idAccesorio;
            $detalle->save();
        }
    }

     private function getVentaByParam($param, $value){
       return DatosVenta::where($param, $value)->first();
    }

    public function getDataVenta($noFactura){
        $query = $this->getVentaByParam('no_factura', $noFactura);

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
        $agencia = match($agencia){ '7051' => '730', '712' => '714', '710' => '710', '333' => null, default => $agencia}; 

        if ($fechaInicio) {
        $fechaInicio = Carbon::parse($fechaInicio)->startOfDay()->format('Y-m-d H:i:s');
        }
        if ($fechaFin) {
            $fechaFin = Carbon::parse($fechaFin)->endOfDay()->format('Y-m-d H:i:s');
        }


        $financiamientos = ComAccesorios::
            with(['vendedor', 'detalles'])
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
    public function getTomaUnidad($estatus = null, $agencia =null, $fechaInicio  = null , $fechaFin  = null, $vendedor = null ){
    

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
        'data' => ComAccesoriosResource::collection($data),
        'estado' => $estatus
    ]);

    }

    public function avanzarEstatus($id)
    {
        $datosVenta = ComAccesorios::find($id);
        if($datosVenta){
            $estatusActual = (int) $datosVenta->estatus;
            $datosVenta->estatus = $estatusActual < 5 ? $estatusActual + 1 : $estatusActual;
            $datosVenta->comentario = null;
            $datosVenta->save();
        }

        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => 'La partida ha avanzado al estado anterior exitosamente'
        ]);
    }
}
