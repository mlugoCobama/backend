<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\SolicitudSurtido as MailSolicitudSurtido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;



use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\OrdenCompra;
use Modules\Compras\Models\DocumentosOrdenesCompra;
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\DetallesCotizacion;
use Modules\Compras\Models\DetalleSolicitud;

use Modules\Compras\Models\Proveedores;

use Modules\Compras\Transformers\DetallesCotizacionResource;
use Modules\Compras\Transformers\DetalleSolicitudCompraResource;


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
     * Genera el registro de orden de compra
     * Actualiza al proveedor seleccionado
     * Actualiza estado de la solicitud
     */
    public function store(Request $request)
    {
        OrdenCompra::create([
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

    public function show($id) // Recupera la orden de compra en base al idCompra
    {
        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();
        $ordenCompra = OrdenCompra::where('cotizaciones_id', $cotizacion->id)->with('documentos')
        ->first(['id','folio_oc', 'fecha', 'observaciones', 'estatus', 'cotizaciones_id']);
        return $ordenCompra;
    }

    public function consultaDatosPDF($id) // Consulta, genera el PDF y envía el PDF ORDEN DE COMPRA
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

    
    public function update(Request $request, $id)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Se ha actualizado correctamente',
            'data' => '',
            'id' => $id,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) // Actualiza el estatus de solicitud y orden de compra a cancelado
    {
        
        SolicitudesCompra::where('id', $id)->update(['estatus' => 5]);
        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();
        OrdenCompra::where('cotizaciones_id', $cotizacion->id)->update(['estatus' => 0]);
        return response()->json([
            'status' => 'success',
            'message' => 'Se ha realizado correctamente',
            'data' => []
        ]);
    }

    /** ?POSIBLE SOLUCION->
     * 
     * 1.- Recupero los datos (id solicitud de compra, id de orden de compra)
     * 2.- Actualizo el Actualizar el estatus de la solicitud de compra a 6 (En surtido)
     * 3.- Actualizar el estatus de la orden de compra a 3 (En surtido)
     * 4.- Generar el registro en documentos compras 
     * 5.- Recupero los datos del proveedor seleccionado para mandarle el correo
     * 6.- Preparo los datos del correo
     * 7.- Mando el correo 
     */
    public function enviarSolicitudSurtido(Request $request){
        $data = $request->all();

        $idOc = $data['idOrdenCompra'];
        $idSc = $data['idSolicituCompra'];

        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $idSc)->first('id');
        $proveedorSeleccionado = CotizacionesProveedores::where('cotizaciones_id', $cotizacion->id)->where('seleccionado', 1)
        ->with(['datos_proveedor' => function($query) {
          $query->select('id', 'nombre', 'correo');
         }])
        ->first(['id','proveedores_id', 'seleccionado']);
        $detallesSC = DetalleSolicitudCompraResource::collection((DetalleSolicitud::where('solicitudes_compra_id', $idSc)->get()));
        
        $datos =  [
            'cotizacion' => $cotizacion,
             'proveedor' => $proveedorSeleccionado,
             'detalles' => $detallesSC,
         ];

         //* Habilitar para envío de correo a proveedor 
        //  Notification::route('mail',
        //                 $datos['proveedor']['datos_proveedor']['correo'])
        //                 ->notify(new SolicitudSurtido($datos));
         
        SolicitudesCompra::where('id', $idSc)->update(['estatus' => 6]);
        OrdenCompra::where('id', $idOc)->update(['estatus' => 3]);

        DocumentosOrdenesCompra::create(['orden_compra_id' => $idOc]);

         return response()->json([
            'status' => 'success',
            'message' => 'Se ha autorizado y enviado el correo
                         al proveedor correctamente',
            'data' => []
        ]);
    }

    /** ?POSIBLE SOLUCION->
     * 
     * 1.- Recuperar los datos (id solicitud de compra, id de orden de compra,)
     * 2.- Actualizar el estatus de la orden de compra a 4 (Autorizada)
     * 4.- Actualizar el estatus de la orden de compra a 2
     * 3.- Generar el registro en documentos compras
     */
    public function autorizarOrden(Request $request){
        $data = $request->all();

        $idOc = $data['idOrdenCompra'];
        $idSc = $data['idSolicituCompra'];

        SolicitudesCompra::where('id', $idSc)->update(['estatus' => 6]);
        OrdenCompra::where('id', $idOc)->update(['estatus' => 3]);

        DocumentosOrdenesCompra::create(['orden_compra_id' => $idOc]);
         return response()->json([
            'status' => 'success',
            'message' => 'Se ha autorizado correctamente',
            'data' => []
        ]);
    }

    public function leerXML($id){
        $rutas = DocumentosOrdenesCompra::where('orden_compra_id', $id)->get();
        
        $rutaXML =  storage_path('app/'.$rutas[0]['ruta_xml_factura']);

        if(!file_exists($rutaXML)){
            return response()->json(['message' => 'Archivo no encontrado']);
        }
        
        $contenidoXML = file_get_contents($rutaXML);

         return response($contenidoXML, 200)
         ->header('Content-Type', 'application/xml');

    }
}
