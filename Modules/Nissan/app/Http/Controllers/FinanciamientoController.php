<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Modules\Nissan\Http\Requests\StoreFinanciamientoRequest;
use Modules\Nissan\Models\ComFinanciamiento;
use Modules\Nissan\Models\DatosVenta;
use Modules\Nissan\Transformers\ComFinanciamientosResource;

class FinanciamientoController extends Controller
{
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
        $comision = $request->filled('id') ? ComFinanciamiento::findOrFail($request->id) : new ComFinanciamiento();
        
        $datosVenta =  $this->getVentaByParam('no_factura', $request->numero_factura);

        $comision->no_contrato           = $request->no_contrato;
        $comision->fecha_desembolso      = $request->fecha_desembolso;
        $comision->numero_factura        = $request->numero_factura;
        $comision->monto_financiar       = $request->monto_financiar;
        $comision->incentivo_dealer      = $request->incentivo_dealer;
        $comision->porcentaje_asesor     = (($request->porcentaje_asesor ?? 0) / 100);
        $comision->comision_asesor_pesos = $request->comision_asesor_pesos;
        $comision->com_vendedores_id     = $request->com_vendedores_id;
        $comision->tipo_financiamiento   = $request->tipo_financiamiento;
        $comision->com_datos_venta_id    = $datosVenta->id ?? null;
        $comision->observaciones         = $request->observaciones;

        if ($request->hasFile('archivo')) {
        if ($comision->ruta_archivo && Storage::disk('public')->exists($comision->ruta_archivo)) {
            Storage::disk('public')->delete($comision->ruta_archivo);
        }

        $ruta = $request->file('archivo')->storeAs('comisiones/financiamientos',$comision->no_contrato.'_'.$comision->numero_factura.'.'.$request->file('archivo')->getClientOriginalExtension() , 'public');
        $comision->ruta_archivo = $ruta;
    }


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

        $financiamientos = ComFinanciamiento::
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
    public function getFinanciaminetos($estatus = null, $agencia =null, $tipoVenta = null, $fechaInicio  = null , $fechaFin  = null, $vendedor = null ){
    

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
