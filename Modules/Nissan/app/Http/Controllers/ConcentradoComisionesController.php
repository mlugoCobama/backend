<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use App\Services\CorteService;

class ConcentradoComisionesController extends Controller
{
    protected $corteService;

    public function __construct(CorteService $corteService)
    {
        $this->corteService = $corteService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = DB::connection('autos')->select('CALL SP_GetConcentadoComisionesTest()');
        return response()->json([
            'status' =>  'success',
            'data' => $query,
            'message' =>  'datos recuperados correctamente'
        ]);
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
        $corteId = $this->corteService->generar(
            $request->fecha_inicio,
            $request->fecha_fin,
            $request->clave_corte,
            $request->agencia,
        );

        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => "Corte del periodo ",
            'corte_id' => $corteId
        ]);
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
    public function update(Request $request, $id): RedirectResponse
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

    public function generarCorte(Request $request)
    {
        
    }

    public function preview(Request $request)
    {
        $data = $this->corteService->preview(
            $request->fecha_inicio,
            $request->fecha_fin
        );

        return response()->json($data);
    }

    public function viewDetallesVendedorRubro($idVendedor, $rubro){
        $data = match ($rubro) {
            'nuevos' => $this->corteService->comisionesAutorizadasNuevos($idVendedor),
            'seminuevos' => $this->corteService->comisionesAutorizadasSeminuevos($idVendedor),
            'accesorios' => $this->corteService->comisionesAutorizadasAccesorios($idVendedor),
            'seguros' => $this->corteService->comisionesAutorizadasSeguros($idVendedor),
            'financimaientos' => $this->corteService->comisionesAutorizadasFinanciamiento($idVendedor),
            'toma_de_unidades' => $this->corteService->comisionesAutorizadasTomaUnidad($idVendedor),
        };

        return response()->json(
            ['status' => 'success',
            'data' => $data,
            'message' => "Detales de $rubro recuperados correctamente"
            ]
        );

    }

    public function devolverPendiente($idRegistro, Request $request){
        $nuevoEstatus = $request->estatus - 1;
        $rubro = $request->rubro;
        $comentario = $request->comentario;

        $this->corteService->setPendienteAutorizacion($rubro, $idRegistro,   $nuevoEstatus, $comentario);

        return response()->json([
            'status' => 'success',
            'message'=> "$rubro actualizado correctamente"
        ]);
    }
}
