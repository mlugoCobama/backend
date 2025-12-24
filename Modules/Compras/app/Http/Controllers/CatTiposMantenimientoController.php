<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Compras\Models\CatTiposMantenimiento;

class CatTiposMantenimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = CatTiposMantenimiento::where('tipo', 2)->get();
        return response()->json([
            'satatus' => 'success',
            'message' => 'Conulta realizada correctamente',
            'data' => $data
        ]);
    }

    public function tiposMantenimientoInfra()
    {
        $data = CatTiposMantenimiento::where('tipo', 2)->get();
        return response()->json([
            'satatus' => 'success',
            'message' => 'Conulta realizada correctamente',
            'data' => $data
        ]);
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
       $data = CatTiposMantenimiento::where('tipo', $id)->get();

        return response()->json([
            'satatus' => 'success',
            'message' => 'Conulta realizada correctamente',
            'data' => $data
        ]);
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
