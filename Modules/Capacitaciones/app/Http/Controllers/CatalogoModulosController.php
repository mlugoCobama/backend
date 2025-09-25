<?php

namespace Modules\Capacitaciones\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Capacitaciones\Models\CatalogoModulosAs;
use Modules\Capacitaciones\Transformers\ModulosAsResource;



class CatalogoModulosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $catModulos = CatalogoModulosAs::with('ModulosSubmodulos')->get();
        // $catModulos = CatalogoModulosAs::with('ModulosSubmodulos.Submodulo')->get();
        // $catModulos = CatalogoModulosAs::get();

        $catModulos = CatalogoModulosAs::with([
            'ModulosSubmodulos.Submodulo',
            'ModulosSubmodulos.funciones'
        ])->get();


        
        return response()->json([
            'status' => 'success',
            'data' =>   ModulosAsResource::collection($catModulos),
            'message' => 'Catálogos obtenidos correctamente'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('capacitaciones::create');
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
        return view('capacitaciones::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('capacitaciones::edit');
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
