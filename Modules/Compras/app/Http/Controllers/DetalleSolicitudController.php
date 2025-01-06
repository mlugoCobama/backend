<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// use Illuminate\Http\Response;
use ZipArchive;

class DetalleSolicitudController extends Controller
{
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
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
    $folderPath = public_path(".expedientes/$id");
    $zipFileName = "expedientes_proveedor_$id.zip";

    // Verifica si la carpeta existe
    if (!file_exists($folderPath)) {
        return response()->json(['error' => 'Carpeta no encontrada'], 404);
    }

    // Crear archivo ZIP en una ubicación temporal
    $zip = new ZipArchive;
    $zipFilePath = public_path($zipFileName);

    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        $files = scandir($folderPath);

        foreach ($files as $file) {
            $filePath = "$folderPath/$file";

            if (is_file($filePath)) {
                $zip->addFile($filePath, $file);
            }
        }

          $zip->close();

    } else {
        return response()->json(['error' => 'No se pudo crear el archivo ZIP'], 500);
    }
    return response()->download($zipFilePath)->deleteFileAfterSend(true);

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
