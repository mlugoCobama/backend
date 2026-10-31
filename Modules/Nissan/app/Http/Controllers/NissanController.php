<?php

namespace Modules\Nissan\Http\Controllers;

use App\Exports\FacturasExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class NissanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('nissan::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('nissan::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required', // Puede ser '707' o '707,705'
            'fecha_inicial' => 'required|date',
            'fecha_final' => 'required|date',
            'formato' => 'nullable|in:excel,csv', // Permite definir si quiere CSV o XLSX
        ]);

        $companias = explode(',', $request->input('id'));
        $fechaInicial = $request->input('fecha_inicial');
        $fechaFinal = $request->input('fecha_final');
        $formato = $request->input('formato', 'excel');

        $agenciaName =  match ((int)$request->input('id')) {
            706 => 'renault',
            // 707 => 'nissan_insurgentes',
            705 => 'nissan_azcapotzalco',
            709 => 'nissan_campestre',
            710 => 'nissan_universidad',
            default => null,
        };

        $fileName = 'Reporte_Facturas_'.$agenciaName.'_' . date('Ymd_His');

        if ($formato === 'csv') {
            return Excel::download(
                new FacturasExport($companias, $fechaInicial, $fechaFinal),
                $fileName . '.csv',
            );
        }


        return Excel::download(
            new FacturasExport($companias, $fechaInicial, $fechaFinal),
            $fileName . '.xlsx'
        );

        // return Excel::download(
        //     new ReporteConcentradoComprasMultihojaExport($detalleData),
        //     $nombreArchivo
        // );
    }

    public function exportar(Request $request)
    {

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
