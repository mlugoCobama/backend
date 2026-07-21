<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OllamaService
{
    /** Convierte una imagen a base 64 */
    public function convertToBase64($imagen){
       return base64_encode(file_get_contents($imagen->getRealPath()));
    }

    /** Envia el promt y la iamgen en base 64 para ser ejecutado por la ia */
    public function processImage($base64, $prompt){

        $response = Http::timeout(300)
        ->acceptJson()
        ->post('http://localhost:11434/api/chat', [
                'model' => 'qwen2.5vl:3b',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                        'images' => [$base64]
                    ]
                ],
            'stream' => false
        ]);
        if (!$response->successful()) {
            return [
                'success' => false,
                'error' => 'Error al comunicarse con Ollama.',
                'status' => $response->status(),
                'response' => $response->body()
            ];

        }

    $respuesta = $response->json();
    $contenido = $respuesta['message']['content'] ?? '';
    $datos = json_decode($contenido, true);

    if (json_last_error() !== JSON_ERROR_NONE) {

        return [
            'success' => false,
            'error' => 'La IA no devolvió un JSON válido.',
            'respuesta_original' => $contenido
        ];
    }

    return [
        'success' => true,
        'datos' => $datos,
        ];
        
    }
}