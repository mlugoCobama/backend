<?php

namespace Modules\Compras\Http\Controllers;

use App\Exports\EasyGasExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Compras\Models\SolcitudDiesel;
use Modules\Compras\Services\DispersionDiesel;
use Modules\Compras\Transformers\ExhibicionRecargaTokaResource;

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
            $solicitud = SolcitudDiesel::findOrFail($id);

            $dispersiones = [];

            for ($i = 1; $i <= $solicitud->exibiciones; $i++) {
                $data = DB::connection('dashboard')
                    ->select('CALL SP_GetDispersionDiesel(?, ?)', [$id, $i]);

                $dispersiones[] = [
                    'numero_exhibicion' => $i,
                    'vehiculos' => ExhibicionRecargaTokaResource::collection($data),
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'solicitud'    => $solicitud,
                    'dispersiones' => $dispersiones,
                ],
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

    public function descargarDispersion($id, $noDispersion =  1){
            $data = DB::connection('dashboard')->select('CALL SP_GetPalntillaDispersionDiesel(?, ?)', [$id, $noDispersion]);
                    return Excel::download(
                        new EasyGasExport($data),
                        'GenerarPedidoDeAltas.xlsx'
                    );
    }

}
