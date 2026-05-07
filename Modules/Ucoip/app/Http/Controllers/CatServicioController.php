<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Compras\Models\Proveedores;
use Modules\Ucoip\Models\CatServicio;

class CatServicioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $servicios = CatServicio::active()->get(['id', 'nombre', 'descripcion']);
        $provedores =  Proveedores::active()->isTi()->get(['id', 'nombre', 'servicios']);

        $data = [
            'servicios' => $servicios,
            'provedores' => $provedores,
        ];

        return response()->json([
            'data' => $data,
            'message' => 'Datos recuperados correctamente',
            'status' => 'success'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('ucoip::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('ucoip::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('ucoip::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
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
