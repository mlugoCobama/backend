<?php

namespace Modules\Compras\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

//Model
use Modules\Compras\Models\ExpedientesProveedores;

//Transformers

//Utilities 
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;


class ExpedientesProveedoresController extends Controller
{
    
    // Función que recupera archivos 
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

    //Genera un Zip con el expediente del proveedor para ser descargado
    public function downloadExpediente($id)
    {
        $rutas = ExpedientesProveedores::where('proveedores_id', $id)
        ->get([
        'constancia_fiscal',
        'ine',
        'comprobante_domicilio',
        'estado_cuenta',
        'acta_constitutiva',
        'poder_notarial',
        ]);

        $archivoCF = $rutas[0]['constancia_fiscal'];
        $archivoINE = $rutas[0]['ine'];
        $archivoCD = $rutas[0]['comprobante_domicilio'];
        $archivoEC = $rutas[0]['estado_cuenta'];
        $archivoAC = $rutas[0]['acta_constitutiva'];
        $archivoPODN = $rutas[0]['poder_notarial'];

        $zip = new ZipArchive();
        $zipFileName = 'Expediente_' . $id . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);
    
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            if (Storage::exists($archivoCF)) {
                $zip->addFile(Storage::path($archivoCF), basename($archivoCF));
            }
            if (Storage::exists($archivoINE)) {
                $zip->addFile(Storage::path($archivoINE), basename($archivoINE));
            }
            if (Storage::exists($archivoCD)) {
                $zip->addFile(Storage::path($archivoCD), basename($archivoCD));
            }
            if (Storage::exists($archivoEC)) {
                $zip->addFile(Storage::path($archivoEC), basename($archivoEC));
            }
            if (Storage::exists($archivoAC)) {
                $zip->addFile(Storage::path($archivoAC), basename($archivoAC));
            }
            if (Storage::exists($archivoPODN)) {
                $zip->addFile(Storage::path($archivoPODN), basename($archivoPODN));
            }
            $zip->close();
            
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo generar el archivo ZIP'
            ], 500);
        }
    
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }


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
