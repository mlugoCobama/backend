<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Nissan\Http\Requests\StoreSeguroRequest;
use Modules\Nissan\Models\ComSeguro;
use Modules\Nissan\Models\DatosVenta;
use Modules\Nissan\Transformers\SeguroResource;

class SegurosController extends Controller
{
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
        $comision = $request->filled('id') ? ComSeguro::findOrFail($request->id) : new ComSeguro();
        
        // $datosVenta =  $this->getVentaByParam('no_factura', $request->numero_factura);

        $comision->com_vendedores_id  = $request->com_vendedores_id;
        $comision->folio      = $request->folio;
        $comision->poliza     = $request->poliza;
        $comision->fecha_emision         = $request->fecha_emision;
        $comision->prima_neta         = $request->prima_neta;
        $comision->comision_apv_pesos = ($request->prima_neta ?? 0) * 0.04;
        $comision->observaciones      = $request->observaciones;

        $comision->save();
        $esNuevo = $comision->wasRecentlyCreated;

        return response()->json([
            'status' => 'success',
            'message' => $esNuevo ? 'Comisión creada correctamente' : 'Comisión actualizada correctamente',
            'data'    => $comision
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
        $datosVenta = ComSeguro::find($id);
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
