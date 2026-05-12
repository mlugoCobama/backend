<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Transformers\AlmacenComprasResource;

class AlmacenComprasConrollerController extends Controller
{

    /**
     * Rcupera los detalles de las compras de ti
     */
    public function index()
    {   
        $query = DB::table('com_solicitudes_compra as sc')
            ->select([
                'sc.id as solicitud_id',
                'sc.folio',
                'sc.empresa',
                'sc.usuario_destino',
                'sc.fecha',
                'sc.com_cat_sistemas_auto_id',
                'cs.sistema as categoria',

                'ds.id as detalle_id',

                'ds.cantidad',
                'um.nombre as unidad',
                'ds.descripcion',
                'ds.observaciones',
                'ds.estatus_almacen'
            ])
            ->join('com_detalle_solicitud as ds', function ($join) {
                $join->on('ds.solicitudes_compra_id', '=', 'sc.id')
                    ->where('ds.confirmado', '=', 1);
            })
            ->join('com_cat_unidades_medida as um', 'ds.cat_unidades_medida_id', '=', 'um.id')
            ->join('com_catalogo_sistemas_auto as cs', 'sc.com_cat_sistemas_auto_id', '=', 'cs.id')
            ->where('sc.activo', 1)
            ->where('sc.estatus', '>', 8)
            ->where('sc.tipo', 3)
            // ->whereIn('sc.com_cat_sistemas_auto_id', [27, 28, 34])
            ->orderBy('sc.usuario_destino')
            ->orderBy('sc.id')
            ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Compras de recursos tecnologico recuperadas correctamete',
                'data' => AlmacenComprasResource::collection($query),
            ]);
    }


    public function create()
    {
        return view('compras::create');
    }


    public function store(Request $request): RedirectResponse
    {
        //
    }

    public function show($id)
    {
        return view('compras::show');
    }


    public function edit($id)
    {
        return view('compras::edit');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function altaAlmacem(){

    }

    public function calcularExistencia(){

    }

    public function actualizarExistencia(){

    }

    public function recuperarMovimientosSolicitud(){

    }

    public function recuperarAlmacenEmpresa(){

    }


}
