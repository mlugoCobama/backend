<?php

namespace Modules\Nissan\Http\Controllers;

use App\Enums\EstatusComisionesAutos;
use App\Enums\EstatusComisionesVentasAutos;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Nissan\Models\ComCorte;
use Modules\Nissan\Services\ConcentradoService;
use Modules\Nissan\Transformers\ConcentradoResource;

class ConcentradoComisionesController extends Controller
{
    protected $corteService;

    public function __construct(ConcentradoService $corteService)
    {
        $this->corteService = $corteService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
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
            $request->comisiones,
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
        $query = DB::connection('autos')->select('CALL SP_GetConcentadoComisionesTesting(?)',[$id]);
        return response()->json([
            'status' =>  'success',
            'data' => ConcentradoResource::collection($query),
            'message' =>  'datos recuperados correctamente'
        ]);
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

    public function getCorte($id){
        $data = DB::connection('autos')->select('CALL SP_GetConcentradoCorte(?)', [$id]);

        return response()->json([
            'data'=> $data,
            'message'=> 'Corte recuperado correctamente',
            'status'=>  'success'
        ]);
    }

    public function getListadoCortes($idAgencia){
        $data = ComCorte::porAgencia($idAgencia)->get();

        return response()->json([
            'data'=> $data,
            'message'=> 'Corte recuperado correctamente',
            'status'=>  'success'
        ]);
    }

    public function viewDetallesVendedorRubro($idVendedor, $rubro){
        $dataAutorizadas = match ($rubro) {
            'nuevos' => $this->corteService->comisionesAutorizadasNuevos($idVendedor, EstatusComisionesVentasAutos::REV_RH),
            'seminuevos' => $this->corteService->comisionesAutorizadasSeminuevos($idVendedor, EstatusComisionesVentasAutos::REV_RH),
            'accesorios' => $this->corteService->comisionesAutorizadasAccesorios($idVendedor, EstatusComisionesAutos::AUTORIZADA),
            'seguros' => $this->corteService->comisionesAutorizadasSeguros($idVendedor, EstatusComisionesAutos::AUTORIZADA),
            'financiamiento' => $this->corteService->comisionesAutorizadasFinanciamiento($idVendedor, EstatusComisionesAutos::AUTORIZADA),
            'toma_de_unidades' => $this->corteService->comisionesAutorizadasTomaUnidad($idVendedor, EstatusComisionesAutos::AUTORIZADA),
            'otros' => $this->corteService->comisionesAutorizadasOtros($idVendedor, EstatusComisionesAutos::AUTORIZADA),
            'otros_descuentos' => $this->corteService->comisionesAutorizadasOtros($idVendedor, EstatusComisionesAutos::AUTORIZADA, 2),
            default => []
        };

        $dataPendientes = match ($rubro) {
            'nuevos' => $this->corteService->comisionesAutorizadasNuevos($idVendedor, EstatusComisionesVentasAutos::EN_ESPERA),
            'seminuevos' => $this->corteService->comisionesAutorizadasSeminuevos($idVendedor, EstatusComisionesVentasAutos::EN_ESPERA),
            'accesorios' => $this->corteService->comisionesAutorizadasAccesorios($idVendedor, EstatusComisionesAutos::EN_ESPERA),
            'seguros' => $this->corteService->comisionesAutorizadasSeguros($idVendedor, EstatusComisionesAutos::EN_ESPERA),
            'financiamiento' => $this->corteService->comisionesAutorizadasFinanciamiento($idVendedor, EstatusComisionesAutos::EN_ESPERA),
            'toma_de_unidades' => $this->corteService->comisionesAutorizadasTomaUnidad($idVendedor, EstatusComisionesAutos::EN_ESPERA),
            'otros' => $this->corteService->comisionesAutorizadasOtros($idVendedor, EstatusComisionesAutos::EN_ESPERA),
            'otros_descuentos' => $this->corteService->comisionesAutorizadasOtros($idVendedor, EstatusComisionesAutos::EN_ESPERA, 2),
            default => []
        };


        $data = [
            'autorizadas' => $dataAutorizadas,
            'pendientes' => $dataPendientes,
        ];

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
        
        if($rubro == 'nuevos' || $rubro == 'seminuevos'){
            $nuevoEstatus = EstatusComisionesVentasAutos::EN_ESPERA;
        }

        $this->corteService->setPendienteAutorizacion($rubro, $idRegistro,   $nuevoEstatus, $comentario);

        return response()->json([
            'status' => 'success',
            'message'=> "$rubro actualizado correctamente"
        ]);
    }

    public function autorizadoPendiente($idRegistro, Request $request){
        $nuevoEstatus = $request->estatus + 1;
        $rubro = $request->rubro;
        $comentario = null;

        if($rubro == 'nuevos' || $rubro == 'seminuevos'){
            $nuevoEstatus = EstatusComisionesVentasAutos::REV_RH;
        }

        $this->corteService->setPendienteAutorizacion($rubro, $idRegistro,   $nuevoEstatus, $comentario);

        return response()->json([
            'status' => 'success',
            'message'=> "$rubro actualizado correctamente"
        ]);
    }
}
