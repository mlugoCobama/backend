<?php

namespace Modules\Compras\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DateTime;
use DateTimeZone;

//Models
use Modules\Compras\Models\OrdenCompra;
use Modules\Compras\Models\DocumentosOrdenesCompra;
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\DetallesCotizacion;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Models\Proveedores;
use Modules\Compras\Models\Cotizaciones;
//Transformers
use Modules\Compras\Transformers\DetallesCotizacionResource;
use Modules\Compras\Transformers\DetalleSolicitudCompraResource;
//Utilities
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
//Mailables
use App\Notifications\SolicitudSurtido;



class OrdenesComprasController extends Controller
{
    /** ************************************************************
     * Genera un nuevo folio consecutivo en base al ultimo folio
     **************************************************************/
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
        // return response()->json(['nuevoFolio' => $nuevoFolio]);
        return $nuevoFolio;
    }

    public function index()
    {
        return view('compras::index');
    }
    public function create()
    {
        return view('compras::create');
    }

    /** **********************************************************
     * Genera la orden de compra en la BD
     ************************************************************/
    public function store(Request $request)
    {

        $validacion = Validator::make($request->all(),[
            // 'folio_oc' => 'required|string|max:50',
            // 'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:150',
            'cotizaciones_id' => 'required',
            'id_cotizacion_prov' => 'required',
            'id_solicitud_compra' => 'required',
        ]);

        if($validacion->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'Datos incompletos o no validos',
                'errors' => $validacion->errors()
            ]);
        }

        try{
            DB::beginTransaction();

                // Genera el registro de orden de compra
                OrdenCompra::create([
                    'folio_oc' => $this->generarFolio(),
                    'fecha' => $this->getFecha() ?? now(),
                    'observaciones' => $request->input('observaciones'),
                    'cotizaciones_id' => $request->input('cotizaciones_id'),
                    'entrega' => $request->input('entrega'),
                ]);

                // Actualiza al proveedor seleccionado
                CotizacionesProveedores::where('id', $request->input('id_cotizacion_prov'))->update(['seleccionado' => 1]);

                // Actualiza estado de la solicitud
                SolicitudesCompra::where('id', $request->input('id_solicitud_compra'))->update(['estatus' => 3]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha actualizado correctamente',
                'data' => []
            ]);

        }catch(\Exception $e){

            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ]);
        }
    }

    /** **********************************************************
     * Recupera la orden de compra en base al idCompra
     ************************************************************/
    public function show($id)
    {
        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();
        $ordenCompra = OrdenCompra::where('cotizaciones_id', $cotizacion->id)->with('documentos')
            ->first(['id', 'folio_oc', 'fecha', 'observaciones', 'estatus', 'cotizaciones_id']);
        return $ordenCompra;
    }

    /** **************************************************************
     * Consulta para generar el formato interno de orden de compra
     ****************************************************************/
    public function consultaDatosPDF($id)
    {
        $solicitudCompra = SolicitudesCompra::where('id', $id)->first();

        $user = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $solicitudCompra->usuario_destino . ')');
        $userSolicita = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $solicitudCompra->usuario_solicita . ')');

        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();

        $ordenCompra = OrdenCompra::where('cotizaciones_id', $cotizacion->id)->first();

        $cotizacionProveedor = CotizacionesProveedores::where('cotizaciones_id', $cotizacion->id) ->where('seleccionado', 1)->first();

        $proveedor = Proveedores::where('id', $cotizacionProveedor->proveedores_id)->first();

        $detalleCotizacion = DetallesCotizacionResource::collection((DetallesCotizacion::where('cotizaciones_proveedores_proveedores_id', $cotizacionProveedor->id)->get()));
        
        $data =  [
            'ordenCompra' => $ordenCompra,
            'cotizacion' => $cotizacion,
            'cotizacionProveedor' => $cotizacionProveedor,
            'proveedor' => $proveedor,
            'detallesCotizacion' => $detalleCotizacion,
            'solicitudCompra' => $solicitudCompra,
            'destino' => $user,
            'solicita' => $userSolicita
        ];

        //Llamada a la funcion quue genera el formato
        $pdf = new OrdenCompraPdfController();
        $file = $pdf->OrdenCompraFormatoInterno($data);

         //Devuelvo el pdf hacia el front
        return response($file, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="orden_compra.pdf');

        // return response()->json(
        //     [
        //         'data' => $data
        //     ]
        //     );
    }

    public function edit($id)
    {
        return view('compras::edit');
    }

    /** ************************************************************
     * Actualiza el estatus a pagado
     ************************************************************/
    public function update(Request $request, $id)
    {
        $idSc = $request->all();

    
        try {
            DB::beginTransaction();
                //Actualiza el estatus de Orden compra a 5 (Pagado)
                OrdenCompra::where('id', $id)->update(['estatus' => 5]);

                //Actualiza el estatus de Solicitud Compra a 7 (Pagado)
                SolicitudesCompra::where('id', $idSc)->update(['estatus' => 7]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha realizado correctamente',
                'data' => $idSc
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ]);
        }
        
    }

    /** ******************************************************************
     * Actualiza el estatus de solicitud y orden de compra a cancelado
     ********************************************************************/
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
                SolicitudesCompra::where('id', $id)->update(['estatus' => 5]);
                $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();
                OrdenCompra::where('cotizaciones_id', $cotizacion->id)->update(['estatus' => 0]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Se ha realizado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ]);

        } 
    }

    /** ********************************************************************************
     * Marca la solicitud como autorizada y envía un correo al proveedor seleccionado
     **********************************************************************************/
    public function enviarSolicitudSurtido(Request $request)
    {
        $data = $request->all();
        
        // Recupero los datos (id solicitud de compra, id de orden de compra)
        $validacion =  Validator::make($data,[
            'idOrdenCompra' => 'required|exists:com_orden_compra,id',
            'idSolicituCompra' => 'required|exists:com_solicitudes_compra,id'

        ]);

        if($validacion->fails()){
            return response()->json([
                "status" => "error",
                "message" => "Datos no validos o incorrectos",
                "errors" => $validacion->errors()
            ]);
        }

        $idOc = $data['idOrdenCompra'];
        $idSc = $data['idSolicituCompra'];

            DB::beginTransaction();

        try {
                $cotizacion = Cotizaciones::where('solicitudes_compra_id', $idSc)->first('id');

                if(!$cotizacion){
                    throw new \Exception('No se encontro la cotizacion asociada');
                }
                // Recupero los datos del proveedor seleccionado para mandarle el correo
                $proveedorSeleccionado = CotizacionesProveedores::where('cotizaciones_id', $cotizacion->id)
                    ->where('seleccionado', 1)
                    ->with(['datos_proveedor' => function ($query) {
                        $query->select('id', 'nombre', 'correo');
                    }])
                    ->first(['id', 'proveedores_id', 'seleccionado']);

                    if(!$proveedorSeleccionado){
                        throw new \Exception('No hay un proveedor seleccionado para esta cotizacion');
                    }

                // Recupero los detalles de la cotizacion para el cuerpo del correo
                $detallesSC = DetalleSolicitudCompraResource::collection((DetalleSolicitud::where('solicitudes_compra_id', $idSc)->get()));

                if($detallesSC->isEmpty()){
                    throw new \Exception('No se encontraron detalles');
                }

                // Datos para el correo
                $datos = [
                    'cotizacion' => $cotizacion,
                    'proveedor' => $proveedorSeleccionado,
                    'detalles' => $detallesSC,
                ];

                /** ********************************************************************************
                *! Habilitar para envío de correo a proveedor
                * ********************************************************************************/ 
                //   Notification::route('mail', $datos['proveedor']['datos_proveedor']['correo'])
                //               ->notify(new SolicitudSurtido($datos));

                // Actualizo el Actualizar el estatus de la solicitud de compra a 6 (En surtido)
                SolicitudesCompra::where('id', $idSc)->update(['estatus' => 6]);

                // Actualizar el estatus de la orden de compra a 3 (En surtido)
                OrdenCompra::where('id', $idOc)->update(['estatus' => 3]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha autorizado y enviado el correo al proveedor correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error y la operación fue revertida',
                'error' => $e->getMessage()
            ]);
        }
    }

    /** ********************************************************************************
     * Marca la solicitud como autorizada y envía un correo al proveedor seleccionado
     **********************************************************************************/
    public function autorizarOrden(Request $request)
    {
        $data = $request->all();

        // Recuperar los datos (id solicitud de compra, id de orden de compra,)
        $idOc = $data['idOrdenCompra'];
        $idSc = $data['idSolicituCompra'];

        try {
            DB::beginTransaction();
                // Actualizar el estatus de la solicitud de compra a 4 (Autorizada)
                SolicitudesCompra::where('id', $idSc)->update(['estatus' => 4]);
                
                // Actualizar el estatus de la orden de compra a 3 (Autorizada)
                OrdenCompra::where('id', $idOc)->update(['estatus' => 3]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha autorizado correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ]);
        }
    }

    /** ************************************************************************************
     * Lee el contenido de archivos xml en el servidor y le envía los datos al frontend
     **************************************************************************************/
    public function leerXML($id)
    {
        // Recupera las ruta del xml en el servidor
        $rutas = DocumentosOrdenesCompra::where('orden_compra_id', $id)->get('ruta_xml_factura');

        $contenidosXML = [];
        // Recupera los contenido de los XML
         foreach($rutas as $ruta){
            $rutaXML =  storage_path('app/' . $ruta['ruta_xml_factura']);
            if (file_exists($rutaXML)) {
                $contenidoXML = file_get_contents($rutaXML);
                $contenidosXML[] = $contenidoXML; 
            }
            else{
                return response()->json(['message' => 'Archivo no encontrado']);
            }
         }

        // Envía el contenido hacia el fronted en json 
          return response()->json(['contenidos' => $contenidosXML], 200)
              ->header('Content-Type', 'application/json');
        // return $contenidosXML;

    }

    /** ************************************************************************************
     * Recupera la fecha en un formato que la bd acepte
     **************************************************************************************/
    public function getFecha(){
        $fecha = new DateTime('now', new DateTimeZone('America/Mexico_City'));
        $fecha = $fecha->format('Y-m-d H:i:s');
        return $fecha;
    }
}
