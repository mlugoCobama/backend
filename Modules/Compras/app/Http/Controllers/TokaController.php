<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Compras\Models\EmpresasToka;
use Modules\Compras\Models\TarjetasToka;
use Modules\Compras\Transformers\TarjetasTokaResource;

class TokaController extends Controller
{
    /**
     * Recupera las tarjetas toka disponibkes de todas las empresas
     */
    public function index()
    {
        $data = [
            'tarjetas' => TarjetasTokaResource::collection(TarjetasToka::active()->with('cliente')->get()),
            'clientes' => EmpresasToka::get(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Catalogo recuperado correctamente'
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
     * Almacena o actualiza datos de una tarjetq toka
     */
    public function store(Request $request)
    {
        $data = $request->all();

        if($data['tipo'] == 'nueva'){
            $existe = TarjetasToka::with('cliente')->where('tarjeta', $data['numeroTarjeta'])->first();
        }else{
            $existe = TarjetasToka::with('cliente')->where('tarjeta', $data['numeroTarjeta'])->where('id','<>',$data['numeroTarjeta'])->first();
        }

        if($existe){
            return response()->json([
                'status' => 'error',
                'message' => 'Esta tarjeta ya esta registrada en la empresa: '.$existe->cliente?->nombre_empresa,
                'data' => []
             ]);
        }

        if($data['tipo'] == 'nueva'){
            $tarjeta = new TarjetasToka();
        }else{
            $tarjeta = TarjetasToka::find($data['id']);
        }
        
        $tarjeta->tarjeta = $data['numeroTarjeta'];
        $tarjeta->proxy_number = $data['proxyNumber'];
        $tarjeta->cuenta = $data['cuenta'];
        $tarjeta->nomina = $data['nomina'];
        $tarjeta->com_empresas_toka_id = $data['empresa'];
        $tarjeta->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Tarjeta Guardada Correctamente',
            'data' => []
        ]);


    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $data = EmpresasToka::where('intercompania', $id)->with(['tarjetasDisponibles'])->get();

        return response()->json([
            'status' => 'success',
            'data' => $data[0]['tarjetasDisponibles'] ?? [],
            'message' => 'Tarjetas recuperadas correctamente'
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
       $tarjeta = TarjetasToka::find($id);
       if(!$tarjeta){
             return response()->json([
                'status' => 'error',
                'message' => 'La tarjeta que intetntas borrar ya no esta disponible',
                'data' => []
             ]);
       }

       $tarjeta->activo = 0;
       $tarjeta->save();
       return response()->json([
            'status' => 'success',
            'message' => 'La tarjeta fue borrada exitosamente',
            'data' => []
        ]);

    }

    public function getClientesToka(){
        $data =  EmpresasToka::get();
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => "Informacion recuperada correctamente"
        ]);
    }
}
