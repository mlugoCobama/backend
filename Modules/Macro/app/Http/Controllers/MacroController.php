<?php

namespace Modules\Macro\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MacroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('macro::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('macro::create');
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
        return view('macro::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('macro::edit');
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

    public function getGaseras(){

        $empresas = DB::connection('intranet')
        ->table('glpi_entities')
            ->select('name','intercompania')
            ->where('intercompania', '>', '0')
            ->where('division', '=', 'ENERGETICOS')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $empresas,
            'message' => 'Datos recuperados correctamente'
        ]);

    }
}
