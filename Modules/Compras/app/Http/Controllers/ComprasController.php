<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\OllamaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class ComprasController extends Controller
{

    public function __construct(
        protected OllamaService $ollama,
        ) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('compras::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('compras::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    set_time_limit(0);

    $request->validate([
        'archivo' => 'required|image|max:10240'
    ]);

    // Convertir la imagen a Base64
    $imagen = $request->file('archivo');
    $base64 = $this->ollama->convertToBase64($imagen);

    $prompt = <<<PROMPT
        Analiza este comprobante de pago.
        Extrae únicamente la siguiente información.
        Responde EXCLUSIVAMENTE con un JSON válido.
        No escribas explicaciones.
        No escribas Markdown.
        No uses ```json.
        No agregues texto antes o después del JSON.
        Si un dato no existe utiliza null.
        {
            "fecha": null,
            "hora": null,
            "banco_origen": null,
            "banco_destino": null,
            "referencia": null,
            "folio": null,
            "monto": null,
            "moneda": null,
            "beneficiario": null,
            "ordenante": null,
            "concepto": null
        }
        PROMPT;

    $result = $this->ollama->processImage($base64, $prompt);

    

    return response()->json([
        'success' => $result['success'],
        'data' => $result['success'] ? $result['datos'] : [],
        'message' => $result['success'] ? 'Comprobante leído correctamente' : $result['error'],
    ]);
}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('compras::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('compras::edit');
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
