<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Http\Requests\StoreSolicitudMacroRequest;
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Transformers\SolicitudesMacroResource;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Models\OrdenTrabajo;
use App\Enums\EstatusSolicitud;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Storage;
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\Proveedores;
use Modules\Compras\Models\DetalleAutotanque;
use Modules\Compras\Services\CotizacionesService;
use Modules\Compras\Transformers\AutotanqueResource;
use Modules\Compras\Transformers\UsersResource;

class SolicitudesMacroController extends Controller
{
    protected $cotizacionesService;
    public function __construct(
        CotizacionesService $cotizacionesService
    ) {
        $this->cotizacionesService = $cotizacionesService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(int $intercompania, ?int $id = null)
    {
        $usuariosCompra = explode(',', env('USERS_MACRO_COMPRAS')); 
        $isCompras = in_array($id, $usuariosCompra );

        $usuariosMacro = explode(',', env('USERS_MACRO_MACRO')); 
        $isMacro = in_array($id, $usuariosMacro );

        $usuariosAdminz = explode(',', env('USERS_COMPRAS_ADMINZ'));
        $isAdminz = in_array($id, $usuariosAdminz );
        
        $usuariosAdmin = explode(',', env('USERS_MACRO_ADMIN')); 
        $isAdmin = in_array($id, $usuariosAdmin );

        if( $isCompras){
            $query = DB::select("call SistemaTickets.SP_GetSolicitudesMacroTesting(?, ?, ?, ?, ?, ?)",[333, 1, 1, 1,'compras',$id]);
            // DB::select("call SistemaTickets.SP_GetSolicitudesMacro()")
            $data = SolicitudesMacroResource::collection($query);
            $tipo = 'compras';
        }

        if($isAdminz){
            $query = DB::select("call SistemaTickets.SP_GetSolicitudesMacroTesting(?, ?, ?, ?, ?, ?)",[null, 1, 1, 1,'adminz',$id]);
            $data = SolicitudesMacroResource::collection($query);
            $tipo = 'compras';
        }

        if($isMacro){
            $query = DB::select("call SistemaTickets.SP_GetSolicitudesMacroTesting(?, ?, ?, ?, ?, ?)",[null, 1, 1, 0,'macro',$id]);
            // DB::select("call SistemaTickets.SP_GetSolicitudesMacroTaller()")
            $data = SolicitudesMacroResource::collection( $query );
            $tipo = 'macro';
        }

        if($isAdmin){
            $query = DB::select("call SistemaTickets.SP_GetSolicitudesMacroTesting(?, ?, ?, ?, ?, ?)",[null, 0, 0, 0,'admin',$id]);
            // DB::select("call SistemaTickets.SP_GetSolicitudesMacroAdmin()")
            $data = SolicitudesMacroResource::collection($query);
            $tipo = 'macro';
        }

        if(!$isMacro && !$isCompras && !$isAdmin && !$isAdminz)
        {
            $query = DB::select("call SistemaTickets.SP_GetSolicitudesMacroTesting(?, ?, ?, ?, ?, ?)",[$intercompania, 0, 0, 0,'empresa',$id]);
            // DB::select("call SistemaTickets.SP_GetSolicitudesMacroGasera($intercompania)")
            $data = SolicitudesMacroResource::collection( $query );
            $tipo = 'gasera';
        }
        
        // $data = SolicitudesMacroResource::collection((SolicitudesCompra::macrotaller()->active()->orderBy('fecha', 'desc')->get()));
        return response()->json([
            'tipo' => $tipo,
            'status' => 'success',
            'message' => 'Consulta generada correctamente',
            'data' => $data
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
    public function store(StoreSolicitudMacroRequest $request)
    {
        $data =  $request->validated()['data'];
        $files = $request->allFiles();

        try {
            DB::beginTransaction();

            $solicitud = $this->storeSolicitudCompra($data);
            // $this->storeDetalleSolicitudCompra($data['detalles'], $idSolicitud, $files);
            $this->storeDetalleSolicitudCompra($data['detalles'], $solicitud['id'], $files, (int)$solicitud['destino'] );

            
            $this->storeOrdenTrabajo($solicitud['id'], $data, $files["file_orden"] );

            if(isset($files["file_cotizacion"]) && !empty($files["file_cotizacion"])){
                $cotizacion = $this->storeCotizacion($solicitud['id']);
                $cotProv = $this->storeCotizacionProveedor($cotizacion);
                $this->storeFile($cotProv, $files["file_cotizacion"]);
            }

            //TODO MODIFICAR ESTO PARA QUE SE ENVIÉ EL CORREO
            //$correos = $this->getGerente($data['empresa']);
            //$this->sendSolicitudAutorizacion($solicitud['id'], $correos);
            DB::commit();

            NotificationHelper::sendNotificationEstatusChange($solicitud['id'], 'Solicitud creada - Es necesario autorizar la solicitud');

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha guardado correctamente',
                'data' => []
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al guardar la solicitud'.($e->getMessage() ?? '') ,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        // $data = SolicitudesMacroResource::collection(DB::select("call SistemaTickets.SP_GetSolicitudMacro($id)"));
        $solicitudCompra = SolicitudesCompra::findOrFail($id);
        if($solicitudCompra->tipo == 2){
            $user = UsersResource::collection(DB::connection('dashboard')->select('call SistemaTickets.SP_GetDataAutotanque('.$solicitudCompra->usuario_destino.')'));
        }else{
            $user = UsersResource::collection(DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $solicitudCompra->usuario_destino . ')'));
        }

        $data =  [
            'ordenCompra' => [],
            'cotizacion' => [],
            'cotizacionProveedor' => [],
            'proveedor' => [],
            'detallesCotizacion' => [],
            'solicitudCompra' => $solicitudCompra,
            'destino' => $user,
            'solicita' => []
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Consulta generada correctamente',
            'data' => $data,
            'dataDestino' => $data['destino'][0]->intercompania,
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
    public function update(Request $request, $id)
    {
        $data = $request->all();

         $solicitudCompra = SolicitudesCompra::findOrFail($id);
            $solicitudCompra->com_cat_sistemas_auto_id = $data['sistema'];
            $solicitudCompra->com_cat_tipos_mantenimiento_id = $data['tipoMantenimiento'];
            $solicitudCompra->auto_macro = 1;
            $hasCotizacion = Cotizaciones::where('solicitudes_compra_id', $id )->first();
            if($hasCotizacion){
                $solicitudCompra->estatus = EstatusSolicitud::EN_COTIZACION;
                NotificationHelper::sendNotificationEstatusChange($solicitudCompra->id, 'En cotización');
            }else{
                $solicitudCompra->estatus = EstatusSolicitud::SOLICITADO;
                NotificationHelper::sendNotificationEstatusChange($solicitudCompra->id, 'Solicitado');
            }
            $solicitudCompra->observaciones = $data['observacion'] ?? null;
            $solicitudCompra->save();

         return response()->json([
            'status' => 'success',
            'message' => 'Datos actualizados exitosamente',
            'data' => []
         ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    /**
   * Genera un folio para cada solicitud de macrotaller
   * 
   * @param string $codigoEntidad 
   *                    intercompania (000) || abreviatura (ABC)
   * @return string $nuevoFolio: folio de tipo MC-IDE-0000X
   */
    public function generarFolioMc($codigoEntidad)
    {
    // Ejemplo: $codigoEntidad = 'GGA'

        $interExcepciones = explode(',', env('INTER_EXECPCIONES')); 
        $isExcepcion = in_array($codigoEntidad, $interExcepciones );

        if($isExcepcion){
            $codigoEntidad = substr($codigoEntidad, 0, 3);
        }

        $prefijo = 'MC-' . strtoupper($codigoEntidad) . '-';

        // Buscar la última orden para ese código de entidad
        $ultimaOrden = SolicitudesCompra::macrotaller()
            ->where('folio', 'like', $prefijo . '%')
            ->active()
            ->orderBy('id', 'desc')
            ->first('folio');

        if ($ultimaOrden) {
            $ultimoFolio = $ultimaOrden->folio;
            $numero = intval(substr($ultimoFolio, strlen($prefijo))) + 1;
        } else {
            $numero = 1;
        }

        $nuevoFolio = $prefijo . str_pad($numero, 5, '0', STR_PAD_LEFT);
        return $nuevoFolio;
    }

  /**
   * Almacena los datos de solicitud de compra
   * @param array  $data Datos del request debe contener:
   * 'empresa (num_intercompania)', 'usuario_solicita (id_usuario)',  
   * 'usuario_destino (id_autotanque)', 'motivo'.
   * @return int : id de la solicitud guardada
   */
    private function storeSolicitudCompra($data)
    {
        $dataSolicitud = new SolicitudesCompra();
        $dataSolicitud->folio = $this->generarFolioMc($data["empresa"]);
        $dataSolicitud->usuario_solicita = $data["usuario_solicita"];
        $dataSolicitud->usuario_destino = $data["usuario_destino"];
        $dataSolicitud->motivo = $data["motivo"];
        $dataSolicitud->empresa = $data["empresa"];
        $dataSolicitud->fecha = date('Y-m-d H:i:s') ?? now();
        $dataSolicitud->c_c = $data["c_c"];
        $dataSolicitud->tipo = 2;
        $dataSolicitud->com_cat_sistemas_auto_id = $data['sistema'];
        if($data['sistema'] == 24){
            $dataSolicitud->auto_macro = 1;
        }
        $dataSolicitud->requiere_anticipo =  ($data["requiere_anticipo"] === "true") ? 1 : 0 ;
        $dataSolicitud->com_cat_tipos_mantenimiento_id = $data['tipoMantenimiento'];
        $dataSolicitud->folio_requisicion = $data['folio_requisicion'];
        $dataSolicitud->save();
        // return $dataSolicitud->id;

         return [
            'id' => $dataSolicitud->id,
            'destino' => $data["usuario_destino"]
            ];
    }

    /**
   * Almacena la orden de trabajo  en la base de datos
   * 
   * @param array $data debe contener 
   *                     "orden_trabajo":string, "usuario_destino":int
   * @param int $idSolicitud de la solicitud de compra
   */
    private function storeOrdenTrabajo($idSolicitud, $data, $file){
        $dataOrdenTrabajo = new OrdenTrabajo();
        $dataOrdenTrabajo->orden_trabajo = $data["orden_trabajo"];

        if(isset($file) && !empty($file)){
            $folderPath = 'ordenesTrabajo/' . $idSolicitud;
            $fileName = $data["orden_trabajo"] . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($folderPath, $fileName);
            $dataOrdenTrabajo->formato_orden = $path;
        }

        $dataOrdenTrabajo->com_datos_vehiculo_id = $data["usuario_destino"];
        $dataOrdenTrabajo->com_solicitudes_compra_id = $idSolicitud;
        $dataOrdenTrabajo->save();
    }

    /**
     * Almacena la cotización adjuntada en la solicitud
     */
    public function storeCotizacion($idSolicitud){
        $folio = $this->cotizacionesService->generarFolioCo();
        $dataCotizacion = new Cotizaciones();
        $dataCotizacion->folio = $folio;
        $dataCotizacion->fecha = date('Y-m-d H:i:s') ?? now();
        $dataCotizacion->consideraciones = "Cotización precargada por la planta";
        $dataCotizacion->solicitudes_compra_id = $idSolicitud;
        $dataCotizacion->save();
        return $dataCotizacion->id;
    }

    public function storeCotizacionProveedor($idCotizacion){
        $proveedor = Proveedores::where('nombre', 'Proveedor Planta Gasera')->first();
        
         $datacotProv = new CotizacionesProveedores();
         $datacotProv->proveedores_id = $proveedor['id'];
         $datacotProv->cotizaciones_id = $idCotizacion;
         
         $datacotProv->save();

         return $datacotProv->id;
    }

    public function storeFile($cotizacionProveedorId, $file){
        $cotizacionProveedor = CotizacionesProveedores::find($cotizacionProveedorId);

        if ($cotizacionProveedor) {
            $folderPath = 'cotizaciones/' . $cotizacionProveedor->cotizaciones_id;
            $fileName = $cotizacionProveedor->proveedores_id . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs($folderPath, $fileName);

            $cotizacionProveedor->update(['ruta' => $path]);
        }
    }




    /**
     * Almacena los detalles de una solicitud de compra en la base de datos.
     *
     * @param $detalles Array de detalles, cada uno debe contener:
     *                         'cantidad', 'descripcion', 'observaciones', 'cat_unidades_medida_id'.
     * @param $idSolicitud ID de la solicitud de compra a la que se asociarán los detalles.
     * @param $files Array de archivos subidos, con claves como 'img_referencia_0', 'img_referencia_1', etc.
     */
    private function storeDetalleSolicitudCompra($detalles, $idSolicitud, $files, $destino)
    {
        foreach ($detalles as $index => $detalle) {
            $detalleSolicitud = new DetalleSolicitud();
            $detalleSolicitud->cantidad = $detalle["cantidad"];
            $detalleSolicitud->descripcion = $detalle["descripcion"];
            $detalleSolicitud->observaciones = $detalle["observaciones"];
            $detalleSolicitud->cat_unidades_medida_id = $detalle["cat_unidades_medida_id"];
            $detalleSolicitud->recuperable = $detalle["recuperar_costo"];

            // Maneja el archivo de imagen
            $fileKey = "img_referencia_" . $index;
            if (isset($files[$fileKey]) && $files[$fileKey]->isValid()) {
                if (in_array($files[$fileKey]->extension(), ['jpg','jpeg','png'])) {
                $path = $files[$fileKey]->store('referencias', 'public');
                $detalleSolicitud->img_referencia = $path;
                }else {
                    throw new \Exception("El archivo debe ser una imagen JPG o PNG");
                }

            }

            $detalleSolicitud->solicitudes_compra_id = $idSolicitud;
            $detalleSolicitud->save();

            if( $destino == 602){
                 $this->storeDetalleAutotanque( $detalle["vehiculo"], $detalleSolicitud->id);
            }
        }
    }

    /**
     * Almacena el detalle del autotanque
     */
    public function storeDetalleAutotanque( $idVehiculo, $idDetalle){
        $detalleAuto = new DetalleAutotanque();
        $detalleAuto->com_detalle_solicitud_id = $idDetalle;
        $detalleAuto->com_datos_vehiculos_id = $idVehiculo;
        $detalleAuto->save();

    } 

    /**
     * Recupera un archivo relacionado con una orden de compra desde el servidor.
     *
     * @param int $id ID de la orden de compra.
     * @param string $file Nombre del archivo que se desea recuperar.
     * @return \Illuminate\Http\Response Archivo solicitado como respuesta HTTP con cabecera Content-Type.
     */
    public function getFile($id, $file)
    {
        $path = storage_path("app/ordenesTrabajo/$id/$file");
        if (!File::exists($path)) {
            abort(404);
        }
        $fileContent = File::get($path);
        $type = File::mimeType($path);
        return response($fileContent, 200)->header("Content-Type", $type);
    }


    public function actualizarSolicitudCompra(Request $request){
        // $data = $request->all();
        $data = json_decode($request->input('data'), true);
        $files = $request->allFiles();
        try {
                DB::beginTransaction();

                $this->updateSolicitudCompra($data, $data['idSolicitud'] );
                $this->updateDetalleSolicitudCompra($data['detalles'],$data['idSolicitud'], $files, (int)$data['usuario_destino'] );
                DB::commit();
                NotificationHelper::sendNotificationEstatusChange($data['idSolicitud'], 'Solicitud modificada-revisa si es necesario realizar alguna acción (Autorizar nuevamente la solicitud)');

                return response()->json([
                    'status' => 'success',
                    'message' => 'Se ha guardado correctamente',
                    'data' => $data
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ocurrió un error al guardar la solicitud' . ($e->getMessage() ?? ''),
                    'error' => $e->getMessage()
                ]);
            }

    }

        /**
 * Actualiza una solicitud de compra existente
 */
private function updateSolicitudCompra($data, $idSolicitud) 
{

    $dataSolicitud = SolicitudesCompra::findOrFail($idSolicitud);
    
    // Actualizar campos
    $dataSolicitud->usuario_solicita = $data["usuario_solicita"];
    $dataSolicitud->empresa = $data["empresa"];
    $dataSolicitud->usuario_destino = $data["usuario_destino"];
    $dataSolicitud->motivo = $data["motivo"];
    $dataSolicitud->c_c = $data["c_c"];
    $dataSolicitud->requiere_anticipo = ($data["requiere_anticipo"] === "true") ? 1 : 0;
    $dataSolicitud->tipo = 2;
    $dataSolicitud->com_cat_sistemas_auto_id = $data['sistema'];
    if($data['sistema'] == 24){
        $dataSolicitud->auto_macro = 1;
    }
    $dataSolicitud->com_cat_tipos_mantenimiento_id = $data['tipoMantenimiento'];
    $dataSolicitud->folio_requisicion = $data['folio_requisicion'];
    $dataSolicitud->motivo_revision = null;
    $dataSolicitud->save();
    
    return [
        'id' => $dataSolicitud->id,
        'destino' => $data["usuario_destino"]
    ];
}

/**
 * Actualiza los detalles de la solicitud de compra
 * Elimina los que no vienen en el request y actualiza/crea los demás
 */
private function updateDetalleSolicitudCompra($detalles, $idSolicitud, $files, $destino) 
{
    $detallesIds = collect($detalles)
        ->filter(function($detalle) {
            return isset($detalle['id']) && !empty($detalle['id']);
        })
        ->pluck('id')
        ->toArray();
    
    if (!empty($detallesIds)) {
        DetalleSolicitud::where('solicitudes_compra_id', $idSolicitud)
            ->whereNotIn('id', $detallesIds)
            ->get()
            ->each(function($detalle) {

                if ($detalle->img_referencia && Storage::disk('public')->exists($detalle->img_referencia)) {
                    Storage::disk('public')->delete($detalle->img_referencia);
                }
                if ($detalle->detalleAutotanque) {
                    $detalle->detalleAutotanque()->delete();
                }
                $detalle->delete();
            });
    } else {
        DetalleSolicitud::where('solicitudes_compra_id', $idSolicitud)
            ->get()
            ->each(function($detalle) {
                if ($detalle->img_referencia && Storage::disk('public')->exists($detalle->img_referencia)) {
                    Storage::disk('public')->delete($detalle->img_referencia);
                }
                $detalle->delete();
            });
    }
    
    foreach ($detalles as $index => $detalle) {
        if (isset($detalle['id']) && !empty($detalle['id'])) {
            $detalleSolicitud = DetalleSolicitud::find($detalle['id']);
            
            if (!$detalleSolicitud) {
                throw new \Exception("Detalle con ID {$detalle['id']} no encontrado");
            }
            
            if ($detalleSolicitud->solicitudes_compra_id != $idSolicitud) {
                throw new \Exception("El detalle {$detalle['id']} no pertenece a esta solicitud");
            }
        } else {
            $detalleSolicitud = new DetalleSolicitud();
            $detalleSolicitud->solicitudes_compra_id = $idSolicitud;
        }
        

        $detalleSolicitud->cantidad = $detalle["cantidad"];
        $detalleSolicitud->descripcion = $detalle["descripcion"];
        $detalleSolicitud->observaciones = $detalle["observaciones"];
        $detalleSolicitud->cat_unidades_medida_id = $detalle["cat_unidades_medida_id"];
        $detalleSolicitud->recuperable = $detalle["recuperar_costo"] ?? $detalle["recuperable"] ?? 0;
        
        if (isset($detalle["confirmado"])) {
            $detalleSolicitud->confirmado = $detalle["confirmado"];
        }
        
        $fileKey = "img_referencia_" . $index;
        if (isset($files[$fileKey]) && $files[$fileKey]->isValid()) {
            if (in_array($files[$fileKey]->extension(), ['jpg','jpeg','png'])) {
                if ($detalleSolicitud->img_referencia && Storage::disk('public')->exists($detalleSolicitud->img_referencia)) {
                    Storage::disk('public')->delete($detalleSolicitud->img_referencia);
                }
                
                $path = $files[$fileKey]->store('referencias', 'public');
                $detalleSolicitud->img_referencia = $path;
            } else {
                throw new \Exception("El archivo debe ser una imagen JPG o PNG");
            }
        }
        
        $detalleSolicitud->save();
    }
}

}
