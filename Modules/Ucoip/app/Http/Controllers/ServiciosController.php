<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Ucoip\Models\Servicio;
use Modules\Ucoip\Services\PagoProgramadoService;

class ServiciosController extends Controller
{

    protected $pagoProgramadoService;

    public function __construct( PagoProgramadoService $pagoProgramadoService )
    {
        $this->pagoProgramadoService = $pagoProgramadoService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data = $this->pagoProgramadoService->getPagos(2026);
        
        return response()->json([
            'data' => $data,
            'status' => 'success',
            'message' => 'Pagos recuperados del año 2026'
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('ucoip::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    $data = $request->validate([
        'empresa' => 'required',
        'servicio' => 'required',
        'proveedor' => 'required',
        'nombre' => 'required|string',
        'descripcion' => 'nullable|string',
        'identificadorExterno' => 'nullable|string',
        'moneda' => 'required|string',
        'periodicidad' => 'nullable|integer|min:0',
        'fechaInicio' => 'required|date',
        'fechaFin' => 'nullable|date',
        'diaCorte' => 'required|integer|min:0|max:31',
        'diaPago' => 'required|integer|min:0|max:31',
        'renovable' => 'boolean',
        'costoBase' => 'required|numeric'
    ]);

    $servicio = new Servicio();
    $servicio->intercompania = $data['empresa'];
    $servicio->proveedor_id = $data['proveedor'];
    $servicio->tipo_servicio_id = $data['servicio'];
    $servicio->nombre = $data['nombre'];
    $servicio->descripcion = $data['descripcion'];
    $servicio->identificador_externo = $data['identificadorExterno'];
    $servicio->costo_base = $data['costoBase'];
    $servicio->moneda = $data['moneda'];
    $servicio->periodicidad = $data['periodicidad'];
    $servicio->fecha_inicio = $data['fechaInicio'];
    $servicio->fecha_fin = $data['fechaFin'];
    $servicio->dia_pago = $data['diaPago'];
    $servicio->dia_corte = $data['diaCorte'];
    $servicio->renovable = $data['renovable'];
    $servicio->save();

    // $servicio = Servicio::create($data);

    // Generar pagos automáticamente
    $this->pagoProgramadoService->generarParaServicio($servicio, 12);

    return response()->json([
        'servicio' => $servicio,
        'message' => 'Servicio creado y pagos generados correctamente'
    ]);
}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {

        // $dataEmpresas = DB::connection('intranet')->select('call SP_GetEmpresas()');

        $data = Servicio::with(['proveedor', 'tipoServicio'])
        // ->where('intercompania', $id)
        ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Servicios recuperados correctamente',
            'data' => $data
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('ucoip::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
