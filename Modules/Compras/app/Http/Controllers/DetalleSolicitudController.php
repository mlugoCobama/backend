<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Models\DocumentosOrdenesCompra;
use Modules\Compras\Services\CfdiService;
use Modules\Compras\Services\OrdenCompraService;

class DetalleSolicitudController extends Controller
{

public function __construct(
        protected CfdiService $cfdiService,
        protected OrdenCompraService $ordenCompraService
        ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    //     $docs = DocumentosOrdenesCompra::whereNotNull('ruta_xml_factura')
    // ->where('ruta_xml_factura', '<>', '')
    // ->get();

    //     foreach ($docs as $doc) {
    //         try {
    //             if (Storage::exists($doc->ruta_xml_factura)) {
    //                 // Parsear el XML
    //                 $factura = $this->cfdiService->parsearDesdeRuta(Storage::path($doc->ruta_xml_factura));

    //                 // Actualizar campos
    //                 $doc->serie      = $factura['serie'];
    //                 $doc->folio      = $factura['folio'];
    //                 $doc->subtotal   = $factura['subtotal'];
    //                 $doc->total      = $factura['total'];
    //                 $doc->emisor_rfc = $factura['emisor_rfc'];
    //                 $doc->save();
    //             }
    //         } catch (\Throwable $e) {
    //             // Registrar el error y continuar con el siguiente documento
    //             Log::error("Error al procesar documento ID {$doc->id}: ".$e->getMessage());
    //             continue;
    //         }
    //     }

        $ordenes = DB::table('com_orden_compra as oc')
        ->join('com_cotizaciones as c', 'oc.cotizaciones_id', '=', 'c.id')
        ->join('com_cotizaciones_proveedores as cp', function($join) {
            $join->on('c.id', '=', 'cp.cotizaciones_id')
                ->where('cp.seleccionado', 1);
        })
        ->join('com_detalle_solicitud as ds', function($join) {
            $join->on('ds.solicitudes_compra_id', '=', 'c.solicitudes_compra_id')
                ->where('ds.confirmado', 1);
        })
        ->join('com_detalles_cotizacion as dc', function($join) {
            $join->on('dc.detalle_solicitud_id', '=', 'ds.id')
                ->on('dc.cotizaciones_proveedores_proveedores_id', '=', 'cp.id');
        })
        ->select('oc.id')
        ->selectRaw('SUM(dc.importe_unitario * ds.cantidad) as total_orden')
        ->groupBy('oc.id')
        ->get();

    foreach ($ordenes as $orden) {
        DB::table('com_orden_compra')
            ->where('id', $orden->id)
            ->update(['total_orden' => ($orden->total_orden * 1.16)]);
    }

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
    public function show($id) {}

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
    public function update(Request $request, $id)
    {
        $data = $request->all();

        /**
         * TODO: MDIFICARLO PARA QUE ACEPTE NUEVOS DETALLES
         * PREPARARLO PARA QUE MANEJE EL LOG DE EVENTOS
         */
        foreach ($data as $item) {
            if (isset($item['id'])) {
                $this->updateDetalleSolicitudCompra($item);
            } else {
                $this->storeDetalleSolicitudCompra($item, $id);
            }
        }

        return response()->json([
            "status" => 'success',
            "message" => 'Actualizado correctamente',
            "data" => []
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
    
    private function storeDetalleSolicitudCompra($detalle, $idSolicitud,)
    {
        $detalleSolicitud = new DetalleSolicitud();
        $detalleSolicitud->cantidad = $detalle["cantidad"];
        $detalleSolicitud->descripcion = $detalle["descripcion"];
        $detalleSolicitud->observaciones = $detalle["observaciones"];
        $detalleSolicitud->cat_unidades_medida_id = $detalle["cat_unidades_medida_id"] ?? $detalle['unidadMedida'];
        $detalleSolicitud->solicitudes_compra_id = $idSolicitud;
        $detalleSolicitud->save();
    }

    private function updateDetalleSolicitudCompra($item)
    {
        DetalleSolicitud::where('id', $item['id'])->update([
            'cantidad' => $item['cantidad'],
            'descripcion' =>  $item['descripcion'],
            'observaciones' =>  $item['observaciones'],
            // 'cat_unidades_medida_id' =>  $item['unidadMedida']['id'],
            'cat_unidades_medida_id' =>  $item['unidadMedida'],
            'solicitudes_compra_id' =>  $item['solicitudes_compra_id'],
            // 'img_referencia' =>  $item['img_referencia'],
            'confirmado' =>  $item['confirmado'],
        ]);
    }

}
