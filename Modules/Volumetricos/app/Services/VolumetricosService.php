<?php

namespace Modules\Volumetricos\Services;

use Modules\Volumetricos\Models\ReporteVolumen;

class VolumetricosService
{

    public function storeReporte($intercompania, $rutaArchivo, $tipoInstalcion, $descripcion, $fechaReporte, $uuid, $rutaPlantilla, $comentarios  ){
        $registro = ReporteVolumen::create([
                'empresa' => $intercompania,
                'ruta_archivo' => $rutaArchivo,
                'ruta_plantilla' => $rutaPlantilla,
                'uuid_plantilla' => $uuid,
                'fecha_reporte' => $fechaReporte,
                'tipo' => $tipoInstalcion,
                'descripcion' => $descripcion,
                'comentarios' => $comentarios,
            ]);

        return $registro;
    }


    public function updateReporte($id, $intercompania, $rutaArchivo, $tipoInstalcion, $descripcion, $fechaReporte, $uuid, $rutaPlantilla, $comentarios)
    {
        $registro = ReporteVolumen::findOrFail($id);

        $registro->update([
            'empresa' => $intercompania,
            'ruta_archivo' => $rutaArchivo,
            'ruta_plantilla' => $rutaPlantilla,
            'uuid_plantilla' => $uuid,
            'fecha_reporte' => $fechaReporte,
            'tipo' => $tipoInstalcion,
            'descripcion' => $descripcion,
            'comentarios' => $comentarios,
        ]);

        return $registro;
    }
}
