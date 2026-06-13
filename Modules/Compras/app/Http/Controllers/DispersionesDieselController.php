<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Services\DispersionDiesel;
use Modules\Compras\Transformers\RecargaTokaResource;

class DispersionesDieselController extends Controller
{
    protected $dispersionDiesel;

    public function __construct( DispersionDiesel $dispersionDiesel)
    {
        $this->dispersionDiesel = $dispersionDiesel;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->dispersionDiesel->getDispersionesDiesel();

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Datos recuperados correctamente'
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
        $data = DB::connection('dashboard')->select('CALL SP_GetDispersionDiesel(?)', [$id]);
        return response()->json([
            'status' => 'success',
            'data' => RecargaTokaResource::collection($data),
            'message' => 'Datos recuperados correctamente'
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
