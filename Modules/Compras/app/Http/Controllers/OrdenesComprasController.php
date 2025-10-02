<?php

namespace Modules\Compras\Http\Controllers;

use App\Enums\EstatusOrdenCompra;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\EstatusSolicitud;

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
use Modules\Compras\Transformers\SolicitudesMacroResource;
//Utilities
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Notification;
//Mailables
use App\Notifications\SolicitudSurtido;
use Modules\Compras\Transformers\AutotanqueResource;
use Modules\Compras\Transformers\OrdenCompraResource;
use Modules\Compras\Transformers\UsersResource;

class OrdenesComprasController extends Controller
{
    /** **********************************************************
     * Genera la orden de compra en la BD
     ************************************************************/
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Genera el registro de orden de compra
            OrdenCompra::create([
                'folio_oc' => $this->generarFolio(),
                'fecha' => now(),
                'observaciones' => $request->input('observaciones'),
                'cotizaciones_id' => $request->input('cotizaciones_id'),
                'entrega' => $request->input('entrega'),
            ]);

            // Actualiza al proveedor seleccionado
            $cotizacionProv = CotizacionesProveedores::find($request->input('id_cotizacion_prov'));
            if ($cotizacionProv) {
                $cotizacionProv->seleccionado = 1;
                $cotizacionProv->save();
            }

            // Actualiza estado de la solicitud
            $solicitud = SolicitudesCompra::find($request->input('id_solicitud_compra'));
            if ($solicitud) {
                $solicitud->estatus = EstatusSolicitud::EN_ORDEN_COMPRA;
                $solicitud->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha actualizado correctamente',
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

    /** **********************************************************
     * Recupera la orden de compra en base al id de cotización
     ************************************************************/
    public function show($id)
    {
        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();
        $ordenCompra = new OrdenCompraResource(OrdenCompra::where('cotizaciones_id', $cotizacion->id)->first());

        return response()->json([
            'data' => $ordenCompra
        ]);        
        
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
                // Actualiza el estatus de OrdenCompra a 5 (Pagado)
                    $orden = OrdenCompra::find($id);
                    if ($orden) {
                        $orden->estatus = EstatusOrdenCompra::PAGADA;
                        $orden->save();
                    }

                    // Actualiza el estatus de SolicitudCompra a 7 (Pagado)
                    $solicitud = SolicitudesCompra::find($idSc);
                    if ($solicitud) {
                        $solicitud->estatus = EstatusSolicitud::PAGADA;
                        $solicitud->save(); 
                    }
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

                SolicitudesCompra::where('id', $id)->update(['estatus' => EstatusSolicitud::CANCELADA]);

                $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();

                OrdenCompra::where('cotizaciones_id', $cotizacion->id)->update(['estatus' => EstatusOrdenCompra::CANCELADA]);
                
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

    public function rechazarOrdenCompra(Request $request)
    {
        $data = $request->all();

        try {
            DB::beginTransaction();

            $solicitud = SolicitudesCompra::find($data['id']);
            if ($solicitud) {
                $solicitud->estatus = EstatusSolicitud::CANCELADA;
                $solicitud->razon_cancelacion = $data['razonCancelacion'];
                $solicitud->save(); // Esto dispara eventos Eloquent
            }

            // Obtener cotización relacionada
            $cotizacion = Cotizaciones::where('solicitudes_compra_id', $data['id'])->first();

            // Actualizar OrdenCompra
            if ($cotizacion) {
                $orden = OrdenCompra::where('cotizaciones_id', $cotizacion->id)->first();
                if ($orden) {
                    $orden->estatus = EstatusOrdenCompra::CANCELADA;
                    $orden->razon_cancelacion = $data['razonCancelacion'];
                    $orden->save(); // También dispara eventos
                }
            }

                
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
                     ->Seleccionado()->with(['datos_proveedor' => function ($query) {
                        $query->select('id', 'nombre', 'correo');
                    }])->first(['id', 'proveedores_id', 'seleccionado']);

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
                Notification::route('mail', $datos['proveedor']['datos_proveedor']['correo'])
                            ->notify(new SolicitudSurtido($datos));

                $solicitud = SolicitudesCompra::find($idSc);
                if ($solicitud) {
                    $solicitud->estatus = EstatusSolicitud::EN_SURTIDO;
                    $solicitud->save(); 
                }

                $orden = OrdenCompra::find($idOc);
                if ($orden) {
                    $orden->estatus = EstatusOrdenCompra::EN_SURTIDO;
                    $orden->save();
                }


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

                $solicitud = SolicitudesCompra::find($idSc);
                if ($solicitud) {
                    $solicitud->estatus = EstatusSolicitud::AUTORIZADA;
                    $solicitud->save();
                }

                $orden = OrdenCompra::find($idOc);
                if ($orden) {
                    $orden->estatus = EstatusOrdenCompra::AUTORIZADA;
                    $orden->save();
                }



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
     * Probablemente en desuso
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

        return $nuevoFolio;
    }

    /** **************************************************************
     * Consulta para generar el formato interno de orden de compra
     ****************************************************************/
    public function consultaDatosPDF($id)
    {
        $solicitudCompra = SolicitudesCompra::where('id', $id)->first();
        if($solicitudCompra->tipo == 2){
            $user = UsersResource::collection(DB::connection('dashboard')->select("call SP_GetDataAutotanque($solicitudCompra->usuario_destino, $solicitudCompra->empresa )"));
        }else{
            $user = UsersResource::collection(DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $solicitudCompra->usuario_destino . ')'));
        }

        $userSolicita = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $solicitudCompra->usuario_solicita . ')');

        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();

        $ordenCompra = OrdenCompra::where('cotizaciones_id', $cotizacion->id)->first();

        $cotizacionProveedor = CotizacionesProveedores::where('cotizaciones_id', $cotizacion->id)->Seleccionado()->first();

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

    }
}
