<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Ucoip\Models\Resguardo;
use Modules\Ucoip\Transformers\ResguardoResource;

class ResguardosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('ucoip::index');
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
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $resguardo = Resguardo::with(['detalles.hardware.tipo'])->where('id_usuario_asignado', $id)->get();

        return response()->json([
            'success' => 'Datos recuperados correctamente',
            'data' => ResguardoResource::collection($resguardo),
        ]);
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
