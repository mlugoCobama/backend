<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Ucoip\Models\Auditoria;
use Modules\Ucoip\Models\Ucoip;
use Modules\Ucoip\Services\AuditoriaUcoipPdfService;

class AuditController extends Controller
{

    private $pdfAuditoriaUcoip;


    public function __construct(
        AuditoriaUcoipPdfService $pdfAuditoriaUcoip,
    ) {
        $this->pdfAuditoriaUcoip = $pdfAuditoriaUcoip;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('ucoip::index');
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
    // $validated = $request->validate([
    //     'ucoip_id' => [
    //         'required',
    //         'exists:ucoip,id'
    //     ],

    //     'fecha_inicio' => [
    //         'required',
    //         'date'
    //     ],

    //     'fecha_fin' => [
    //         'nullable',
    //         'date',
    //         'after_or_equal:fecha_inicio'
    //     ],

    //     'observaciones' => [
    //         'nullable',
    //         'string'
    //     ],

    //     'detalles' => [
    //         'required',
    //         'array',
    //         'min:1'
    //     ],

    //     'detalles.*.tipo' => [
    //         'required',
    //         'in:hardware,sistema,recurso_red,licenciamiento'
    //     ],

    //     'detalles.*.referencia_id' => [
    //         'nullable',
    //         'integer'
    //     ],

    //     'detalles.*.resultado' => [
    //         'required',
    //         'in:correcto,diferencia,no_localizado,no_asignado,no_aplica'
    //     ],

    //     'detalles.*.datos' => [
    //         'nullable',
    //         'array'
    //     ],

    //     'detalles.*.observaciones' => [
    //         'nullable',
    //         'string'
    //     ],
    // ]);
    $validated = $request->all();
    DB::beginTransaction();
    try {
        $auditoria = Auditoria::create([
            'ucoip_ucoip_id' => $validated['ucoip_id'],
            'responsable_id' => auth()->id(),
            'fecha' => now(),
            // 'fecha_inicio' => $validated['fecha_inicio'],
            // 'fecha_fin' => $validated['fecha_fin'] ?? now(),
            // 'estatus' => 'completada',
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        foreach ($validated['detalles'] as $detalle) {
            $auditoria->detalles()->create([
                'tipo' => $detalle['tipo'],
                'referencia_id' => $detalle['referencia_id'] ?? null,
                'resultado' => $detalle['resultado'],
                'datos' => $detalle['datos'] ?? null,
                'observaciones' => $detalle['observaciones'] ?? null,
            ]);
        }

        DB::commit();

        return response()->json([
            'status' => 'success',
            'data' => $auditoria->load('detalles'),
            'message' => 'Auditoría guardada correctamente.',
        ], 201);

    } catch (\Throwable $e) {

        DB::rollBack();

        throw $e;
    }
}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $auditoias = Auditoria::active()->with('responsable')->where('ucoip_ucoip_id', $id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $auditoias,
            'message' => 'Auditorias recuperadas correctamente'
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
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

    }

    public function imprimirAuditoria($id){

        $auditoria = Auditoria::with([
                    'ucoip.userGlpi', 'ucoip.empresa',
                    'responsable', 'detalles',
                ])->findOrFail($id);

                // return response()->json([
                //     'data' => $auditoria
                // ]);

        $file = $this->pdfAuditoriaUcoip->generarAuditoria($auditoria);

        return response($file, 200)
            ->header('Content-Type', 'application/pdf');
    }

    public function getAllUcoip($id)
    {
        $ucoip = Ucoip::active()->where('id',$id )->with(
                            'userGlpi',
                            'empresa',
                            'activos.hardware.tipoHardware',
                            'sistemas.sistema',
                            'recursosRed.recursoRed',
                            'licenciamientos.licencia',
                            'tokens.token'
                            )->first();


     return response()->json([
             'status' => 'success',
             'data' => $ucoip,
             'message' => 'Ucoip recuperado correctamente'
         ]);
    }

    public function printFormato($id){
        $ucoip = Ucoip::active()->where('id',$id )->with(
                            'userGlpi',
                            'empresa',
                            'activos.hardware.tipoHardware',
                            'sistemas.sistema',
                            'recursosRed.recursoRed',
                            'licenciamientos.licencia',
                            'tokens.token'
                            )->first();

            $file = $this->pdfAuditoriaUcoip
                ->generar($ucoip);

            return response($file, 200)
            ->header('Content-Type', 'application/pdf');
    }
}
