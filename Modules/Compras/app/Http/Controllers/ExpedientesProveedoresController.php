<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

//Model
use Modules\Compras\Models\ExpedientesProveedores;

//Transformers

//Utilities 
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;


class ExpedientesProveedoresController extends Controller
{

    /** ******************************************************************
     * Función que recupera archivos 
     ********************************************************************/
    public function getFile($id, $file)
    {
        $path = storage_path("app/expedientes/$id/$file");
        if (!File::exists($path)) {
            abort(404);
        }
        $fileContent = File::get($path);
        $type = File::mimeType($path);
        return response($fileContent, 200)->header("Content-Type", $type);
    }

    /** ********************************************************************
     * Genera un Zip con el expediente del proveedor para ser descargado optimizacion
     *********************************************************************/
    public function downloadExpediente($id)
    {
        $archivos = ['constancia_fiscal', 'ine', 'comprobante_domicilio', 'estado_cuenta', 'acta_constitutiva', 'poder_notarial'];
        $rutas = (ExpedientesProveedores::where('proveedores_id', $id)->first($archivos));

        if (!$rutas) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontraron archivos'
            ], 404);
        }

        //Valido que existan las rutas y cuento los archivos     
        $archivosDisponibles = $this->validarExpediente($rutas, $archivos);

        if (count($archivosDisponibles) > 0) {
            //Genero el zip si hay archivos disponibles
            $zipPath =  $this->generarZip($archivosDisponibles, $rutas, $id);

        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontraron archivos'
            ], 404);
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function validarExpediente($rutas, $archivos)
    {
        $archivosDisponibles = [];
        foreach ($archivos as $archivo) {
            if (!empty($rutas[$archivo])) {
                $archivosDisponibles[] = $archivo;
            }
        }
        return $archivosDisponibles;
    }

    public function generarZip($archivosDisponibles, $rutas, $id)
    {
        $zip = new ZipArchive();
        $zipFileName = 'Expediente_' . $id . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($archivosDisponibles as $archivo) {
                if (Storage::exists($rutas[$archivo])) {
                    $zip->addFile(Storage::path($rutas[$archivo]), basename($rutas[$archivo]));
                }
            }
            $zip->close();
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo generar el archivo ZIP'
            ], 500);
        }
        return $zipPath;
    }

    public function store(Request $request): RedirectResponse
    {
        //
    }
    public function show($id)
    {
        //
    }
    
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }
    public function destroy($id)
    {
        //
    }
}
