<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\SolicitudSurtido as MailSolicitudSurtido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;



use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\OrdenCompra;
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\DetallesCotizacion;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Models\Proveedores;

use Modules\Compras\Transformers\DetallesCotizacionResource;
use Modules\Compras\Transformers\DetalleSolicitudCompraResource;
use setasign\Fpdi\Fpdi;

use App\Notifications\SolicitudSurtido;
use Illuminate\Support\Facades\Notification;

class OrdenesComprasController extends Controller
{
    //Genera un nuevo folio consecutivo en base al ultimo folio
    public function generarFolio()
    {
        $ultimaOrden = OrdenCompra::orderBy('id', 'desc')->first();
        if ($ultimaOrden) {
            $ultimoFolio = $ultimaOrden->folio_oc;
            $numero = intval(substr($ultimoFolio, 3)) + 1;
        } else {
            $numero = 1;
        }
        $nuevoFolio = 'OC-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
        return response()->json(['nuevoFolio' => $nuevoFolio]);
    }

    // Función para generar el PDF de la orden de compra  
    public function generarPDFOc(Request $request)
    {
        // $data = $request->all();

        // $pdf = Pdf::loadView('pdf_orden_compra', $data);
        // // return $data;
        // return $pdf->download('archivo.pdf');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('compras::index');
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
    public function store(Request $request)
    {

        $ordenCompra = OrdenCompra::create([
            'folio_oc' => $request->input('folio_oc'),
            'fecha' => $request->input('fecha'),
            'observaciones' => $request->input('observaciones'),
            'cotizaciones_id' => $request->input('cotizaciones_id'),
        ]);

        CotizacionesProveedores::where('id', $request->input('id_cotizacion_prov'))->update(['seleccionado' => 1]);
        SolicitudesCompra::where('id', $request->input('id_solicitud_compra'))->update(['estatus' => 3]);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha actualizado correctamente',
            'data' => ''
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();
        $ordenCompra = OrdenCompra::where('cotizaciones_id', $cotizacion->id)
        ->first(['folio_oc', 'fecha', 'observaciones', 'estatus', 'cotizaciones_id']);
        return $ordenCompra;
    }

    public function consultaDatosPDF($id)
    {
        $solicitudCompra = SolicitudesCompra::where('id', $id)->first();
        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();
        $ordenCompra = OrdenCompra::where('cotizaciones_id', $cotizacion->id)->first();
        $cotizacionProveedor = CotizacionesProveedores::where('cotizaciones_id', $cotizacion->id)->where('seleccionado', 1)->first();
        $proveedor = Proveedores::where('id', $cotizacionProveedor->proveedores_id)->first();
        $detalleCotizacion = DetallesCotizacionResource::collection((DetallesCotizacion::where('cotizaciones_proveedores_proveedores_id', $cotizacionProveedor->id)->get()));
        $data =  [
            'ordenCompra' => $ordenCompra,
            'cotizacion' => $cotizacion,
            'cotizacionProveedor' => $cotizacionProveedor,
            'proveedor' => $proveedor,
            'detallesCotizacion' => $detalleCotizacion,
            'solicitudCompra' => $solicitudCompra,
        ];
        $pdf = new OrdenCompraPdfController();
        $file = $pdf->OrdenCompraFormatoInterno($data);
        return response($file, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="orden_compra.pdf');
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
    public function update(Request $request, $id)
    {
        $idOC = $request->all();
        SolicitudesCompra::where('id', $id)->update(['estatus' => 4]);
        OrdenCompra::where('cotizaciones_id', $idOC)->update(['estatus' => 2]);
        return response()->json([
            'status' => 'success',
            'message' => 'Se ha realizado correctamente',
            'data' => ''
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        SolicitudesCompra::where('id', $id)->update(['estatus' => 5]);
        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();
        OrdenCompra::where('cotizaciones_id', $cotizacion->id)->update(['estatus' => 0]);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha realizado correctamente',
            'data' => ''
        ]);
    }

    public function enviarSolicitudSurtido(Request $request){
        $id = $request->all();
        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first('id');
        $proveedorSeleccionado = CotizacionesProveedores::where('cotizaciones_id', $cotizacion->id)->where('seleccionado', 1)
        ->with(['datos_proveedor' => function($query) {
            $query->select('id', 'nombre', 'correo');
        }])
        ->first(['id','proveedores_id', 'seleccionado']);
        $detallesSC = DetalleSolicitudCompraResource::collection((DetalleSolicitud::where('solicitudes_compra_id', $id)->get()));
        
        $datos =  [
            'cotizacion' => $cotizacion,
            'proveedor' => $proveedorSeleccionado,
            'detalles' => $detallesSC,
        ];
        SolicitudesCompra::where('id', $id)->update(['estatus' => 6]);
        OrdenCompra::where('cotizaciones_id', $cotizacion->id)->update(['estatus' => 3]);
         //Notification::route('mail', $datos['proveedor']['datos_proveedor']['correo'])->notify(new SolicitudSurtido($datos));
         return response()->json([
            'status' => 'success',
            'message' => 'Se ha realizado correctamente',
            'data' => $datos
        ]);
    }
}
