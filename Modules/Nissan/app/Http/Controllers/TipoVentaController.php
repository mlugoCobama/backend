<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Nissan\Models\TipoVenta;

class TipoVentaController extends Controller
{
    /**
     * Recupera los tipos de venta activos
     */
    public function index()
    {
        $data = TipoVenta::active()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'datos recuperados correctamente',
            'data' => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Almacena el registro de un nuevo tipo de venta
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $tabulador  = new TipoVenta();
        $tabulador->nombre = $data['nombre'];
        $tabulador->porcentaje = (int) $data['porcentaje'] > 0 ? (int) $data['porcentaje'] / 100 : 0;
        $tabulador->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Tabulador guardado correctamente',
            'data' => []
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('nissan::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('nissan::edit');
    }

    /**
     * Actualiza el registro del tipo de venta
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $tabulador  = TipoVenta::find($id);
        $tabulador->nombre = $data['nombre'];
        $tabulador->porcentaje = (int) $data['porcentaje'] > 0 ? (int) $data['porcentaje'] / 100 : 0;
        $tabulador->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Tabulador actualizado correctamente',
            'data' => []
        ]);
    }

    /**
     * Marca como inactivo el registro del tipo de venta
     */
    public function destroy($id)
    {
        $tabulador  = TipoVenta::find($id);
        $tabulador->activo = 0;
        $tabulador->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Tabulador eliminado correctamente',
            'data' => []
        ]);
    }
}
