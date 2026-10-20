<?php

namespace Modules\Nissan\Services;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\File;

class IaService
{

    // protected $promptService;


    // public function __construct(PromtService $promptService) {
    //     $this->promptService = $promptService;
    // }

    public function procesarDocumentoConGemini($file, string $filename, array $promptVars = [])
    {
        // Obtener el prompt desde el archivo .md
        $prompt =  File::get(base_path('Modules/Nissan/resources/assets/prompts/' . $filename.'.md'));

        // Determinar el MIME type dinámicamente
        $mimeType = $file->getMimeType();

        // Crear el Blob con los datos del archivo
        $fileBlob = new Blob(
            mimeType: MimeType::from($mimeType),
            data: base64_encode(file_get_contents($file->getRealPath()))
        );

        //
        // retry(NúmeroDeIntentos, Callback, MilisegundosDeEspera, CallbackDeFiltroDeExcepcion)
        $response = retry(3, function () use ($prompt, $fileBlob) {

            return Gemini::generativeModel(model:  env('GEMNI_CURRENT_MODEL', 'gemini-3.1-flash-lite'))
                ->generateContent([$prompt, $fileBlob]);

        }, 5000, function (\Exception $exception) {
            return
                str_contains($exception->getMessage(), 'high demand')
                ||str_contains($exception->getMessage(), 'cURL error 28')
                || str_contains($exception->getMessage(), '500')
                || str_contains($exception->getMessage(), '503');
        });

        $textoRespuesta = $response->text();

        // Limpiar formato Markdown
        $jsonLimpio = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($textoRespuesta));

        $datos = json_decode($jsonLimpio, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('No se pudo interpretar la respuesta del modelo como un JSON válido.');
        }

        return $datos;
    }
}
