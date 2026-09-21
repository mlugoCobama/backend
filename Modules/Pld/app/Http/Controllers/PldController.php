<?php

namespace Modules\Pld\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Pld\Services\GeminiService;
use Modules\Pld\Services\ValidadorService;

class PldController extends Controller
{
    protected $validadorService;
    protected $geminiService;

    public function __construct(
        ValidadorService $validadorService,
        GeminiService $geminiService,
    ) {
        $this->validadorService = $validadorService;
        $this->geminiService = $geminiService;
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pld::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pld::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'ine' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
        'curp' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
        'csf' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
        'comprobante_domicilio' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
    ]);

    // Map entre la clave enviada en el Request y su archivo de prompt correspondiente
    $mapaDocumentos = [
        'ine'                   => 'ine.md',
        'curp'                  => 'curp.md',
        'csf'                   => 'csf.md',
        'comprobante_domicilio' => 'comprobante_domicilio.md',
    ];

    $resultados = [];
    $errores = [];

    // 2. Iterar sobre cada documento que el usuario haya enviado
    foreach ($mapaDocumentos as $campoInput => $promptFile) {
        if ($request->hasFile($campoInput)) {
            try {
                $file = $request->file($campoInput);

                // Procesar usando nuestra función genérica
                $resultados[$campoInput] = $this->geminiService->procesarDocumentoConGemini($file, $promptFile);
            } catch (\Exception $e) {
                $errores[$campoInput] = 'Error al procesar: ' . $e->getMessage();
            }
        }
    }

    $evaluacion = $this->validadorService->evaluarExpediente($resultados);

    // 3. Devolver los datos consolidados
    return response()->json([
        'status' => empty($errores) ? 'success' : 'partial_success',
        'data' => ['data' => $resultados, 'evaluacion' => $evaluacion],
        'errors' => $errores
    ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('pld::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('pld::edit');
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
}
