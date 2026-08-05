<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Ucoip\Models\DetalleMantenimiento;
use Modules\Ucoip\Models\EvidenciaMantenimiento;
use Modules\Ucoip\Models\HardwareMantenimiento;
use Modules\Ucoip\Models\MantenimientoChecklist;
use Modules\Ucoip\Http\Requests\StoreMantenimientoRequest;
use Modules\Ucoip\Models\EvidenciaManteninimiento;

class MantenimientoController extends Controller
{
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
     * Store a newly created resource in storage.d
     */
    public function store(StoreMantenimientoRequest $request)
    {
        $payload = $request->payload();

        $mantenimiento = DB::transaction(function () use ($payload, $request) {

            $mantenimiento = HardwareMantenimiento::create([
                'tipo'              => $payload['tipo'],
                'fecha'             => $payload['fecha'],
                'diagnostico'       => $payload['diagnostico'],
                'falla'             => $payload['falla'],
                'comentarios'       => $payload['comentarios'] ?? null,
                'observaciones'     => $payload['observaciones'] ?? null,
                'id_tecnico'        => $payload['realizado_por'],
                'ucoip_hardware_id' => $payload['hardware_id'],
            ]);

            foreach (($payload['checklist'] ?? []) as $item) {
                MantenimientoChecklist::create([
                    'ucoip_hardware_mantenimiemtos_id' => $mantenimiento->id,
                    'cat_checklist_mantenimiento_id'   => $item['cat_checklist_mantenimiento_id'],
                    'completado'                       => $item['completado'],
                ]);
            }

            foreach (($payload['piezas'] ?? []) as $pieza) {
                DetalleMantenimiento::create([
                    'ucoip_hardware_mantenimientos_id' => $mantenimiento->id,
                    'descripcion'        => $pieza['descripcion'] ?? null,
                    'origen'             => $pieza['origen'] ?? null,
                    'no_serie_anterior'  => $pieza['no_serie_anterior'] ?? null,
                    'no_serie_nueva'     => $pieza['no_serie_nueva'] ?? null,
                    'cantidad'           => $pieza['cantidad'] ?? null,
                    'costo_unitario'     => $pieza['costo_unitario'] ?? null,
                    'costo_total'        => $pieza['costo_total'] ?? null,
                    'observacion'        => $pieza['observacion'] ?? null,
                ]);
            }

            $this->guardarEvidencias($request, $mantenimiento->id, 'evidencia_antes', 1);
            $this->guardarEvidencias($request, $mantenimiento->id, 'evidencia_despues', 2);

            return $mantenimiento;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Mantenimiento guardado correctamente',
            'data'    => [],
        ], 201);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $mantenimiento = HardwareMantenimiento::with(['detalles', 'checklist.catalogo', 'evidencias'])
            ->find($id);

        if (! $mantenimiento) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Mantenimiento no encontrado',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $this->formatearMantenimiento($mantenimiento),
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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    private function guardarEvidencias($request, int $mantenimientoId, string $inputName, int $tipo): void
    {
        if (! $request->hasFile($inputName)) {
            return;
        }

        foreach ($request->file($inputName) as $archivo) {
            $ruta = $archivo->store('mantenimientos/' . $mantenimientoId, 'public');

            EvidenciaManteninimiento::create([
                'ucoip_hardware_mantenimiemtos_id' => $mantenimientoId,
                'tipo' => $tipo, // 1 = antes, 2 = después
                'ruta' => $ruta,
            ]);
        }
    }



    private function formatearMantenimiento(HardwareMantenimiento $mantenimiento): array
    {
        $checklist = $mantenimiento->checklist
            ->filter(fn ($item) => $item->catalogo !== null)
            ->mapWithKeys(fn ($item) => [
                $item->catalogo->codigo_control => (bool) $item->completado,
            ]);

        $evidenciaAntes = $mantenimiento->evidencias
            ->where('tipo', 1)
            ->map(fn ($e) => ['id' => $e->id, 'url' => Storage::disk('public')->url($e->ruta)])
            ->values();

        $evidenciaDespues = $mantenimiento->evidencias
            ->where('tipo', 2)
            ->map(fn ($e) => ['id' => $e->id, 'url' => Storage::disk('public')->url($e->ruta)])
            ->values();

        return [
            'id'                => $mantenimiento->id,
            'hardware_id'       => $mantenimiento->ucoip_hardware_id,
            'tipo'              => (string) $mantenimiento->tipo,
            'fecha'             => $mantenimiento->fecha,
            'realizado_por'     => $mantenimiento->id_tecnico,
            'comentarios'       => $mantenimiento->comentarios,
            'observaciones'     => $mantenimiento->observaciones,
            'falla'             => $mantenimiento->falla,
            'diagnostico'       => $mantenimiento->diagnostico,
            'checklist'         => $checklist,
            'piezas'            => $mantenimiento->detalles,
            'evidencia_antes'   => $evidenciaAntes,
            'evidencia_despues' => $evidenciaDespues,
        ];
    }
}
