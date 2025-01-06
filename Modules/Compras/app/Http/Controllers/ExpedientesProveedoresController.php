<?php

namespace Modules\Compras\Http\Controllers;
use Illuminate\Support\Facades\File;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use  Modules\Compras\app\Models\ExpedientesProveedores;

use Illuminate\Support\Facades\Storage;
use ZipArchive;


class ExpedientesProveedoresController extends Controller
{
    

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
