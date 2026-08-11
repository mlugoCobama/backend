<?php

namespace Modules\Volumetricos\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class FileService
{
// public function almacenarArchivo($file, $empresa, $tipo, $fechaReporte)
//     {
//         $fecha = Carbon::parse($fechaReporte);
//         $extension = $file->getClientOriginalExtension();

//         $empresaLimpia = str_replace('-', '', $empresa);
//         $tipoLimpio = str_replace('-', '', $tipo);

//         $nombreArchivo = 'empresa_' .
//             $empresaLimpia . '_' .
//             $tipoLimpio . '_' .
//             $fecha->format('Ymd') . '_' .
//             now()->format('His') . '.' .
//             $extension;

//         return $file->storeAs('volumenes', $nombreArchivo, 'public');
//     }

/**
     * Guarda un archivo cargado desde un Request ($file es UploadedFile)
     */
    public function almacenarArchivo($file, $empresa, $tipo, $fechaReporte)
    {
        $extension = $file->getClientOriginalExtension();
        $nombreArchivo = $this->generarNombreArchivo($empresa, $tipo, $fechaReporte, $extension);

        return $file->storeAs('volumenes', $nombreArchivo, 'public');
    }

    /**
     * Guarda un texto (como un string JSON) directamente como archivo en disco public
     */
    public function almacenarContenido($contenido, $empresa, $tipo, $fechaReporte, $extension = 'json')
    {
        $nombreArchivo = $this->generarNombreArchivo($empresa, $tipo, $fechaReporte, $extension);
        $rutaRelativa = 'volumenes/' . $nombreArchivo;

        // Guarda el contenido de texto en el disco 'public'
        Storage::disk('public')->put($rutaRelativa, $contenido);

        return $rutaRelativa;
    }

    /**
     * Método auxiliar para unificar la nomenclatura de archivos
     */
    private function generarNombreArchivo($empresa, $tipo, $fechaReporte, $extension)
    {
        $fecha = Carbon::parse($fechaReporte);

        $empresaLimpia = str_replace('-', '', $empresa);
        $tipoLimpio = str_replace('-', '', $tipo);

        return 'empresa_' .
            $empresaLimpia . '_' .
            $tipoLimpio . '_' .
            $fecha->format('Ymd') . '_' .
            now()->format('His') . '.' .
            $extension;
    }
}
