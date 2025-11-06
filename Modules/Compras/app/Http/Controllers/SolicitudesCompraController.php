<?php

namespace Modules\Compras\Http\Controllers;

use App\Enums\EstatusOrdenCompra;
use App\Http\Controllers\Controller;
use App\Enums\EstatusSolicitud;
use App\Exports\SolicitudesExport;
use Illuminate\Http\Request;

//Models
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\DetallesCotizacion;
use Modules\Compras\Models\Proveedores;

//Transformers
use Modules\Compras\Transformers\DetalleSolicitudCompraResource;
use Modules\Compras\Transformers\SolicitudesComprasResource;
//Utilities
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

// Mailiables
use App\Mail\SolicitudCotizacion;
use App\Notifications\SolicitudCotizacionNotification;
use Modules\Compras\Notifications\AutorizacionEmail;
// Jobs
use App\Jobs\EnviarCorreoSolicitudCotizacion;
use App\Models\LogEventos;
use Maatwebsite\Excel\Facades\Excel;
//Request validation
use Modules\Compras\Http\Requests\StoreSolicitudCompraRequest;
use Modules\Compras\Http\Requests\SendSolicitudCotizacionRequest;
use Modules\Compras\Models\OrdenCompra;
use Modules\Compras\Transformers\EmailAutorizarSolicitud;
use Modules\Compras\Transformers\EmailAutorizarSolicitudResource;
use Modules\Compras\Transformers\SeguimientoResource;

class SolicitudesCompraController extends Controller
{

    /** ************************************************************
     * Recupera todos los registros de la base de datos
     **************************************************************/
    public function index(int $intercompania, ?int $id = null)
    {
        $usuariosCompra = array_flip([2039, 2364, 1796]);
        $isCompras = isset($usuariosCompra[$id]);

        $usuariosAdmin = array_flip([2395]);
        $isAdmin = isset($usuariosAdmin[$id]);

        $usuariosRT = array_flip([413, 2404, 1796]);
        $isRT = isset($usuariosRT[$id]);    

        $usuariosTG = array_flip([394 , 169]);
        $isTG = isset($usuariosTG[$id]); 
        
        if($isRT){
            
            $query = $this->getSolicitudesCompras(null , 1, 1, 3, null);
            // (SolicitudesCompra::rtecnologicos()->active()->autorizadas()->orderBy('updated_at', 'desc')->get())
            $data = SolicitudesComprasResource::collection( $query );
            $tipo = 'RT';


        }

        if($isCompras){
            $query = $this->getSolicitudesCompras(null , 1, 1, 1, null);
            // (SolicitudesCompra::compras()->active()->autorizadas()->orderBy('updated_at', 'desc')->get())
            $data = SolicitudesComprasResource::collection( $query );
            $tipo = 'compras';
        }

        if($isAdmin){
            $query = $this->getSolicitudesCompras(null , 0, 0, null, null);
            // (SolicitudesCompra::administrador()->active()->orderBy('updated_at', 'desc')->get())
            $data = SolicitudesComprasResource::collection(
            $query    
            );
            $tipo = 'compras';
        }

        if($isTG){
            $query = $this->getSolicitudesCompras(null, 1, 1, 1, 394);
            $data = SolicitudesComprasResource::collection( $query);
            $tipo = 'empresa';

        }
        
        if(!$isRT && !$isCompras && !$isAdmin  && !$isTG){
            $query = $this->getSolicitudesCompras($intercompania , 0, 0, null, null);
            // (SolicitudesCompra::compras()->where('empresa', $intercompania)->active()->orderBy('fecha', 'desc')->get())
            $data = SolicitudesComprasResource::collection(
                $query
            );
            $tipo = 'empresa';
        }
        
        return response()->json([
            'tipo' => $tipo,
            'status' => 'success',
            'message' => 'Consulta generada correctamente',
            'data' => $data
        ]);
    }

    /**
     * Recupera las solicitudes de compra con el folio y total de la orden de compra
     * 
     * @param mixed $intercompania Numero de intercompania de la empresa
     * @param mixed $autoga Autorizacion de gerencia administrativa (0 o 1)
     * @param mixed $autogg Autorizacion de gerencia (0 o 1)
     * @param mixed $tipoSolicitud tipo de solicitud (1= compras, 2 = rt, null = ambas)
     * @param mixed $idUserObjetivo usuario objetivo (null = no aplica el filtro)
     */
    public function getSolicitudesCompras($intercompania, $autoga, $autogg, $tipoSolicitud, $idUserObjetivo){
        return DB::select('CALL SP_GetSolicitudesCompras(?, ?, ?, ?, ?)', [ $intercompania , $autoga, $autogg, $tipoSolicitud, $idUserObjetivo]);
    }

    /** *************************************************************************************
     * Genera un el registro de la solicitud de compra junto con sus detalles
     * Valida y coordina el funcionamiento de storeSolicitudCOmpra y storeDetallesSolicitud
     ***************************************************************************************/
    public function store(StoreSolicitudCompraRequest $request)
    {
        $data =  $request->validated()['data'];
        $files = $request->allFiles();

        try {
            DB::beginTransaction();

            $solicitud = $this->storeSolicitudCompra($data);
            $this->storeDetalleSolicitudCompra($data['detalles'], $solicitud['id'], $files, (int)$solicitud['destino'] );
            //TODO MODIFICAR ESTO PARA QUE SE ENVIÉ EL CORREO
            //$correos = $this->getGerente($data['empresa']);
            //?$this->sendSolicitudAutorizacion($solicitud['id'], $correos);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha guardado correctamente',
                'data' => []
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al guardar la solicitud',
                'error' => $e->getMessage()
            ]);
        }
    }

    
    /** *******************************************************************
     * Recupera los detalles de la solicitud
     *********************************************************************/
    public function show($id)
    {
        return DetalleSolicitudCompraResource::collection((DetalleSolicitud::where('solicitudes_compra_id', $id)->get()));
    }


    /**
     * Actualiza los estatus de la solicitud de compra
     * auto_gg, auto_admin y estatus
     */
    public function update(Request $request, $id)
    {
        // SolicitudesCompra::where('id', $id)->update(
        //     ["$request->campo" => $request->value]
        // );

        $solicitud = SolicitudesCompra::find($id);
            $campo = $request->campo;
            $valor = $request->value;

            $solicitud->$campo = $valor;
            $solicitud->save();

        $this->validarCotizacion($id);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Se ha actualizado correctamente',
            'data' => []
        ]);
    }

    /** ************************************************************
     * Actualiza el estatus a cancelado
     **************************************************************/
    public function destroy($id)
    {

        $solicitudCompra = SolicitudesCompra::find($id);

        if (!$solicitudCompra) {
            return response()->json([
                'status' => 'error',
                'message' => 'El registro que intentas actualizar no existe',
                'data' => []
            ]);
        }

        $solicitudCompra->update([
            'estatus' => EstatusSolicitud::CANCELADA
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => []
        ]);
    }


    public function cancelarSolicitud(Request $request)
    {
        $data = $request->all();

        $solicitudCompra = SolicitudesCompra::find($data['id'] ?? $data[0]);

        if (!$solicitudCompra) {
            return response()->json([
                'status' => 'error',
                'message' => 'El registro que intentas actualizar no existe',
                'data' => []
            ]);
        }

        $solicitudCompra->estatus = EstatusSolicitud::CANCELADA;
        $solicitudCompra->razon_cancelacion = $data['razonCancelacion'] ?? null;
        $solicitudCompra->save();

        $cotizacion = Cotizaciones::where('solicitudes_compra_id ', $solicitudCompra->id)->first();
        if($cotizacion){
            $ordenCompra = OrdenCompra::where('cotizaciones_id', $cotizacion->id)->first();
            if($ordenCompra){
                $ordenCompra->estatus = EstatusOrdenCompra::CANCELADA;
                $ordenCompra->razon_cancelacion = $data['razonCancelacion'] ?? null;
                $ordenCompra->save();
            }
        }   

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => []
        ]);
    }

    /** *********************************************************** 
     * Genera un nuevo folio consecutivo en base al ultimo folio
     *************************************************************/
    public function generarFolioSc()
    {
        $ultimaOrden = SolicitudesCompra::administrador()->orderBy('id', 'desc')->first('folio');
        if ($ultimaOrden) {
            $ultimoFolio = $ultimaOrden->folio;
            $numero = intval(substr($ultimoFolio, 3)) + 1;
        } else {
            $numero = 1;
        }
        $nuevoFolio = 'SC-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
        return  $nuevoFolio;
    }
    /** *******************************************************************
     * Recupera las solicitud de compra por id
     *********************************************************************/
    public function getSolicitud($id)
    {
        $data = new SolicitudesComprasResource(SolicitudesCompra::findOrFail($id));

        return response()->json([
            'status' => 'success',
            'message' => 'Consulta generada correctamente',
            'data' => $data
        ]);
    }

    /** ************************************************************
     * Recupera todos los registros de la base de datos
     * Con paginacion (30 registros por pagina)
     ***************************************************************/
    public function index1(Request $request)
    {

        $perPage = $request->input('perPage', 30);

        $solicitudes = (SolicitudesCompra::active()
            ->paginate($perPage));
        return response()->json([
            'data' => $solicitudes->items(),
            'pagination' => [
                'current_page' => $solicitudes->currentPage(),
                'last_page' => $solicitudes->lastPage(),
                'per_page' => $solicitudes->perPage(),
                'total' => $solicitudes->total(),
            ]
        ]);
    }

    /** ********************************************************************
     *Primero genero una solicitud de compra
     *Después almaceno los detalles de la solicitud
     **********************************************************************/
    private function storeSolicitudCompra($data)
    {
        $usuariosInfra = array_flip([2395, 1939, 1965, 1687, 2296, 413]);
        $isInfra = isset($usuariosInfra[$data["usuario_solicita"]]);

        $dataSolicitud = new SolicitudesCompra();
        $dataSolicitud->folio = $this->generarFolioSc();
        $dataSolicitud->usuario_solicita = $data["usuario_solicita"];
        $dataSolicitud->empresa = $data["empresa"];
        $dataSolicitud->usuario_destino = $data["usuario_destino"];
        $dataSolicitud->motivo = $data["motivo"];
        $dataSolicitud->fecha = date('Y-m-d H:i:s') ?? now();
        $dataSolicitud->c_c = $data["c_c"];
                
        if($isInfra){
           $dataSolicitud->tipo = 3;
        }

        $dataSolicitud->save();
        return [
            'id' => $dataSolicitud->id,
            'destino' => $data["usuario_destino"]
            ];
    }

    /** ***************************************************************************
     * Amacena los detalles de la solicitud
     *****************************************************************************/
    private function storeDetalleSolicitudCompra($detalles, $idSolicitud, $files, $destino)
    {
        foreach ($detalles as $index => $detalle) {
            $detalleSolicitud = new DetalleSolicitud();
            $detalleSolicitud->cantidad = $detalle["cantidad"];
            $detalleSolicitud->descripcion = $detalle["descripcion"];
            $detalleSolicitud->observaciones = $detalle["observaciones"];
            $detalleSolicitud->cat_unidades_medida_id = $detalle["cat_unidades_medida_id"];

            // Maneja el archivo de imagen
            $fileKey = "img_referencia_" . $index;
            if (isset($files[$fileKey]) && $files[$fileKey]->isValid()) {
                $path = $files[$fileKey]->store('referencias', 'public');
                $detalleSolicitud->img_referencia = $path;
            }

            $detalleSolicitud->solicitudes_compra_id = $idSolicitud;
            $detalleSolicitud->save();
        }
    }


    /** ******************************************************************
     * Envía la solicitud de cotización a los proveedores y almacena la 
     * relación en la BD
     ********************************************************************/
    public function enviarSolicitudCotizacion(SendSolicitudCotizacionRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            // Almacenar la cotización
            $idCotizacion = $this->storeCotizacion($data);

            // Obtener los proveedores completos desde la base de datos
            $proveedoresIds = $data['proveedores'];
            $proveedores = Proveedores::whereIn('id', $proveedoresIds)->get();

            // Verificar que todos los proveedores tengan correo asignado
            $proveedoresSinCorreo = $proveedores->filter(function ($proveedor) {
                return empty($proveedor->correo);
            });

            if ($proveedoresSinCorreo->isNotEmpty()) {
                DB::rollback();
                $nombres = $proveedoresSinCorreo->pluck('nombre')->implode(', ');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Algunos proveedores no tienen correo asignado',
                    'errors' => [
                        'proveedores_sin_correo' => $nombres
                    ]
                ], 422);
            }

            $data['proveedores'] = $proveedores->toArray();
            $data['detalles'] = DetalleSolicitud::where("solicitudes_compra_id", $data['solicitudes_compra_id'])
                ->confirmadas()
                ->get();

            // Almacenar la relación entre cotización y proveedores
            $this->storeCotizacionProveedores($proveedores, $idCotizacion);
            //Queue para despachar el correo
            //!Habiltar para que se envíen los correos EnviarCorreoSolicitudCotizacion::dispatch($data); 
            /** *****************************************************************************************
             * !Habiltar para que se envíen los correos 
             * 
             *******************************************************************************************/
            // $this->enviaCorreoProveedores($proveedores, $data);
            // Actualiza el estatus de la Solicitud a COTIZACION
            $idSolicitudC = $data['solicitudes_compra_id'];
            $solicitud = SolicitudesCompra::find($idSolicitudC);
            $solicitud->estatus = EstatusSolicitud::EN_COTIZACION;
            $solicitud->save();
            // SolicitudesCompra::where('id', $idSolicitudC)->update(['estatus' => EstatusSolicitud::EN_COTIZACION]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Correos enviados correctamente',
                'data' => [
                    'cotizacion_id' => $idCotizacion,
                    'proveedores_count' => $proveedores->count(),
                    'solicitud_id' => $idSolicitudC
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al procesar la solicitud',
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    /** ***********************************************************************
     * Función que genera folios consecutivos de las cotizaciones
     ************************************************************************/
    public function generarFolioCo()
    {
        $ultimaCotizacion = Cotizaciones::orderBy('id', 'desc')->first('folio');
        if ($ultimaCotizacion) {
            $ultimoFolio = $ultimaCotizacion->folio;
            $numero = intval(substr($ultimoFolio, 3)) + 1;
        } else {
            $numero = 1;
        }
        $nuevoFolio = 'CO-' . str_pad($numero, 5, '0', STR_PAD_LEFT);

        return $nuevoFolio;
    }

    /**
     * Recupera las autorizaciones por medio del correo 
     * Valida el numero de autorizaciones necesarias
     * Modifica los campos necesarios en la base de datos 
     */
    public function autorizeFromEmail($campo, $necesarias, $id)
    {
        $campoBD = "auto_$campo";
        $solicitud = SolicitudesCompra::findOrFail($id);
        if ($necesarias === 1) {
            $solicitud->auto_admin = "1";
            $solicitud->auto_gg = "1";
            $solicitud->save();
        } else {
            $solicitud->$campoBD = "1";
            $solicitud->save();
        }
        $this->validarAutorizacion($id);

        return view('compras::confirmacion');
    }

    /** 
     * Verifica si ya esta autorizado por gerencias 
     * y actualiza el estatus a siguiente
     */
    public function validarAutorizacion($id)
    {
        $solicitud = SolicitudesCompra::findOrFail($id);
        if ($solicitud->auto_admin === 1 && $solicitud->auto_gg === 1 && $solicitud->tipo === 1) {
            $solicitud->estatus = EstatusSolicitud::SOLICITADO;
            $solicitud->save();
        }

        if ($solicitud->auto_admin === 1 && $solicitud->auto_gg 
            && $solicitud->auto_macro === 1 && $solicitud->tipo === 2) {
            $solicitud->estatus = EstatusSolicitud::SOLICITADO;
            $solicitud->save();
        }
    }

    public function validarCotizacion($id)
    {
        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();

        if(!empty($cotizacion)){
            $cotizacionesDisponibles = CotizacionesProveedores::where('cotizaciones_id', $cotizacion->id)->get();
            $solicitud = SolicitudesCompra::findOrFail($id);
            if ($solicitud->auto_admin === 1 && $solicitud->auto_gg === 1 ) {
            $solicitud->estatus = EstatusSolicitud::EN_COTIZACION;
            $solicitud->save();
            }

            // if(!empty($cotizacionesDisponibles)){
            //     foreach ($cotizacionesDisponibles as $cotizacion) {
            //         CotizacionesProveedores::where('id', $cotizacion->id)->where('ruta','!=','null')->update(['autorizado' => 1]);
            //     }
            // }

            if (!empty($cotizacionesDisponibles)) {
                foreach ($cotizacionesDisponibles as $cotizacion) {
                    $modelo = CotizacionesProveedores::find($cotizacion->id);

                    if ($modelo && $modelo->ruta !== null) {
                        $modelo->autorizado = 1;
                        $modelo->save();
                    }
                }
            }


        }
    }
    /** ***********************************************************************
     * Almacena la cotización y devuelve el id del registro creado
     ************************************************************************/
    public function storeCotizacion($data)
    {
        $dataCotizacion = new Cotizaciones();
        $dataCotizacion->folio = $this->generarFolioCo();
        $dataCotizacion->fecha = now();
        $dataCotizacion->consideraciones = $data["consideraciones"] ?? null;
        $dataCotizacion->solicitudes_compra_id = $data["solicitudes_compra_id"];
        $dataCotizacion->save();
        
        return $dataCotizacion->id;
    }

    /** ***************************************************************************
     * Función que almacena la relación entre cotización y proveedores
     *****************************************************************************/
    public function storeCotizacionProveedores($proveedores, $idCotizacion)
    {
        $idsCotProv = [];
        
        foreach ($proveedores as $proveedor) {
            $datacotProv = new CotizacionesProveedores();
            $datacotProv->proveedores_id = $proveedor->id;
            $datacotProv->cotizaciones_id = $idCotizacion;
            $datacotProv->save();
            
            $idsCotProv[] = $datacotProv->id;
        }
        
        return $idsCotProv;
    }

    /** ***************************************************************************
     * Función que  envía el correo de solicitud de cotización a los proveedores
     ****************************************************************************/
    public function enviaCorreoProveedores($proveedores, $data)
    {
        foreach ($proveedores as $proveedor) {
        if (!empty($proveedor->correo)) {
                try {
                    Notification::route('mail', $proveedor->correo)
                        ->notify(new SolicitudCotizacionNotification($data));
                } catch (\Exception $e) {
                    // \Log::error("Error al enviar correo a proveedor {$proveedor->id}: " . $e->getMessage());
                }
            }
    }
    }

    /**
     * Genera los detalles de cotización y les asigna un cero por defecto
     */
    public function storeDetallesCotizacion($data, $idsCotProv)
    {
        foreach ($data['detalles'] as $detalle) {
            foreach ($idsCotProv as $idDataCotProv) {
                $detalleCotizacion = new DetallesCotizacion();
                $detalleCotizacion->detalle_solicitud_id = $detalle['id'];
                $detalleCotizacion->cotizaciones_proveedores_proveedores_id = $idDataCotProv;
                //$detalleCotizacion->precio_unitario = $detalle['precio_unitario'] ?? 0; 
                $detalleCotizacion->save();
            }
        }
    }


    /**
     * Envía el correo de solicitud de autorización
     * agrega los parámetros para la url del botón 'Autorizo'
     */
    public function sendSolicitudAutorizacion($id, $correos)
    {
        $data = (new EmailAutorizarSolicitudResource(SolicitudesCompra::findOrFail($id)))->resolve();
        $data['autoNecesarias'] = $correos['autoNecesarias'];
        foreach ($correos['data'] as $correo) {
            $email = $correo['name'];
            $data['campo'] = $correo['campo'];
            Notification::route('mail', $email)
                ->notify(new AutorizacionEmail($data));
        }
    }

    /**
     * Recupera los datos de los gerente de las empresas en base a 
     * su numero de intercompania 
     */
    private function getGerente($intercompania)
    {
        $interAgencias = array_flip([7064, 7063, 7062, 7061]);
        $isRenault = isset($interAgencias[$intercompania]);
        if ($isRenault) {
            $intercompania = 7064;
        }
        $data = DB::connection('intranet')->select('call SOPORTEZM.SP_GetGereneciaEmpresas(' . $intercompania . ')');
        $subCadena = 'gerencia';
        $autoNecesarias  = count($data);
        if ($autoNecesarias > 0) {
            foreach ($data as $dato) {
                $isGerente = strpos($dato->name, $subCadena);
                if ($isGerente !== false) {
                    $dato->isGerente = true;
                    $dato->campo = 'gg';
                } else {
                    $dato->isGerente = $isGerente;
                    $dato->campo = 'admin';
                }
            }
            return [
                'data' =>  $data,
                'autoNecesarias' => $autoNecesarias
            ];
        }
    }


    /**
     * Recupera lo eventos de actualización de estatus de la solicitud de compra
     * @param mixed $idSolicitud id de solicitud de orden de compra
     */
    public function getSeguimientoSolicitud($idSolicitud){
        $eventos = LogEventos::where('table_name', 'com_solicitudes_compra')->where('record_id', $idSolicitud)->get();

        return response()->json([
            'status' => 'success',
            'data' => SeguimientoResource::collection($eventos),
            'message' => 'datos recuperados correctamente'
        ]);


    }

    /**
     * Descarga un listado de solicitudes de orden de compra en formato excel 
     */
    public function downloadSolicitudes1()
    {
        $solicitudes = SolicitudesCompra::with('DetallesSolicitud')->where('estatus', '2')->where('tipo', '1')->get()->map(function ($solicitud) {
            $empresas =
                [
                    333    =>    'CORPORACION ADMINISTRATIVA DEL SUR', 201    =>    'AGRUPAMIENTO',
                    131    =>    'AZTECA GAS', 130    =>    'SATELITE GAS', 251    =>    'FLAMAMEX',
                    210    =>    'REYES GAS', 155    =>    'GASAMEX', 135    =>    'SEGAS', 110    =>    'GARZA GAS',
                    111    =>    'GARZA SUR', 250    =>    'GAS FLAMAZUL', 132    =>    'GAS PREMIO',
                    200    =>    'TANQUES SONI', 119    =>    'TANQUES GARZA GAS', 190    =>    'ZUGAS',
                    133    =>    'GASERA MULTIREGIONAL', 353    =>    'GAS URBANO', 710    =>    'NISSAN UNIVERSIDAD',
                    7051    =>    'NISSAN AZCAPOTZALCO', 712    =>    'NISSAN CAMPESTRE', 700    =>    'CORPORATIVO AUTOS SONI',
                    240    =>    'SERVIGAS DEL VALLE', 2000    =>    'SERVICIO EL ONCE', 7064    =>    'RENAULT AZCAPOTZALCO',
                    7062    =>    'RENAULT ECATEPEC', 7063    =>    'RENAULT VALLEJO',7061    =>    'RENAULT PACHUCA',
                    191    =>    'BARAGAS', 354    =>    'IZTAGAS Y ENERGIA',
                ];

            return [
                'Folio' => $solicitud->folio,
                'Fecha' => date('d/m/Y H:i', strtotime($solicitud->fecha)),
                'Empresa' => $empresas[$solicitud->empresa],
                'Estado' => $solicitud->estatus,
                'Detalles' => $solicitud->DetallesSolicitud->map(function ($detalle) {
                    return
                        "Cantidad: " . ($detalle->cantidad ?? '0') . ' ' .
                        "Descripción: " . ($detalle->descripcion ?? '') . ' ' .
                        "Observaciones: " . ($detalle->observaciones ?? '') . ' ' .
                        "Unidad: " . ($detalle->unidadMedida->nombre ?? '') . ' ';
                })->implode("\n"),
            ];
        });

        return Excel::download(new SolicitudesExport($solicitudes), 'solicitudes_compras_generales.xlsx');
    }

    public function downloadSolicitudes( $tipo, $estatus )
    {
        $empresas = [
            333 => 'CORPORACION ADMINISTRATIVA DEL SUR', 201 => 'AGRUPAMIENTO',
            131 => 'AZTECA GAS', 130 => 'SATELITE GAS', 251 => 'FLAMAMEX',
            210 => 'REYES GAS', 155 => 'GASAMEX', 135 => 'SEGAS', 110 => 'GARZA GAS',
            111 => 'GARZA SUR', 250 => 'GAS FLAMAZUL', 132 => 'GAS PREMIO',
            200 => 'TANQUES SONI', 119 => 'TANQUES GARZA GAS', 190 => 'ZUGAS',
            133 => 'GASERA MULTIREGIONAL', 353 => 'GAS URBANO', 710 => 'NISSAN UNIVERSIDAD',
            7051 => 'NISSAN AZCAPOTZALCO', 712 => 'NISSAN CAMPESTRE', 700 => 'CORPORATIVO AUTOS SONI',
            240 => 'SERVIGAS DEL VALLE', 2000 => 'SERVICIO EL ONCE', 7064 => 'RENAULT AZCAPOTZALCO',
            7062 => 'RENAULT ECATEPEC', 7063 => 'RENAULT VALLEJO', 7061 => 'RENAULT PACHUCA',
            191 => 'BARAGAS', 354 => 'IZTAGAS Y ENERGIA',
        ];

        $tipos = [
            1 => 'compras_grales',
            2 => 'compras_macro',
            3 => 'compras_rt',
        ];

        $hoy = date('d_m_Y');

        $solicitudes = SolicitudesCompra::with('DetallesSolicitud.unidadMedida')
            ->where('estatus', $estatus)
            ->where('tipo', $tipo)
            ->get()
            ->flatMap(function ($solicitud) use ($empresas) {
                
                $detalles = $solicitud->DetallesSolicitud;
                return $detalles->map(function ($detalle, $index) use ($solicitud, $empresas) {
                    $labels = EstatusSolicitud::labels();
                    $label = $labels[$solicitud->estatus] ?? 'DESCONOCIDO';
                    return [
                        'Folio'        => $index === 0 ? $solicitud->folio : '',
                        'Fecha'        => $index === 0 ? date('d/m/Y H:i', strtotime($solicitud->fecha)) : '',
                        'Empresa'      => $index === 0 ? ($empresas[$solicitud->empresa] ?? 'N/A') : '',
                        'Estado'       => $index === 0 ? $label : '',
                        'Cantidad'     => $detalle->cantidad ?? 0,
                        'Descripción'  => $detalle->descripcion ?? '',
                        'Observaciones'=> $detalle->observaciones ?? '',
                        'Unidad'       => $detalle->unidadMedida->nombre ?? '',
                    ];
                });
            });

        $filename = 'SC_'.$hoy.'_'.$estatus.'_'.$tipos[$tipo].'.xlsx';
        return Excel::download(
            new SolicitudesExport($solicitudes),
            $filename,
            null,
            ['Content-Disposition' => 'attachment; filename="'.$filename.'"']
        );
    }


}
