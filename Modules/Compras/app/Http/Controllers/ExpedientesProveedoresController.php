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

    
    /**
    * Recupera un archivo específico de un expediente dado su ID y nombre de archivo.
    *
    * @param int $id ID del expediente.
    * @param string $file Nombre del archivo a recuperar.
    * @return \Illuminate\Http\Response El archivo con su tipo MIME, o error 404 si no existe.
    */
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

    /**
    * Genera un archivo ZIP con el expediente del proveedor, incluyendo los archivos disponibles.
    *
    * @param int $id ID del proveedor.
    * @return \Illuminate\Http\Response Descarga del archivo ZIP o mensaje de error si no hay archivos.
    */
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

    /**
    * Valida qué archivos del expediente existen y están disponibles.
    *
    * @param object $rutas Objeto con rutas de los archivos.
    * @param array $archivos Lista de nombres de archivos a verificar.
    * @return array Lista de nombres de archivos disponibles.
    */
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

    /**
    * Genera un archivo ZIP con los archivos disponibles del expediente.
    *
    * @param array $archivosDisponibles Lista de nombres de archivos disponibles.
    * @param object $rutas Objeto con rutas de los archivos.
    * @param int $id ID del proveedor.
    * @return string Ruta del archivo ZIP generado.
    */
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
}
