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
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\Proveedores;
use Modules\Compras\Models\DetalleAutotanque;
use Modules\Compras\Transformers\AutotanqueResource;
use Modules\Compras\Transformers\UsersResource;

class SolicitudesMacroController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(int $intercompania, ?int $id = null)
    {
        $usuariosMacro = array_flip([170, 167, 371, 381, 1796]);
        $usuariosCompra = array_flip([413, 2039, 2364, 1796]);
        $isCompras = isset($usuariosCompra[$id]);
        $isMacro = isset($usuariosMacro[$id]);

        $usuariosAdmin = array_flip([2395]);
        $isAdmin = isset($usuariosAdmin[$id]);

        if( $isCompras){
            $query = DB::select("call SistemaTickets.SP_GetSolicitudesMacroTest(?, ?, ?, ?)",[null, 1, 1, 1 ]);
            // DB::select("call SistemaTickets.SP_GetSolicitudesMacro()")
            $data = SolicitudesMacroResource::collection($query);
            $tipo = 'compras';
        }

        if($isMacro){
            $query = DB::select("call SistemaTickets.SP_GetSolicitudesMacroTest(?, ?, ?, ?)",[null, 1, 1, 0 ]);
            // DB::select("call SistemaTickets.SP_GetSolicitudesMacroTaller()")
            $data = SolicitudesMacroResource::collection( $query );
            $tipo = 'macro';
        }

        if($isAdmin){
            $query = DB::select("call SistemaTickets.SP_GetSolicitudesMacroTest(?, ?, ?, ?)",[null, 0, 0, 0 ]);
            // DB::select("call SistemaTickets.SP_GetSolicitudesMacroAdmin()")
            $data = SolicitudesMacroResource::collection($query);
            $tipo = 'macro';
        }

        if(!$isMacro && !$isCompras && !$isAdmin)
        {
            $query = DB::select("call SistemaTickets.SP_GetSolicitudesMacroTest(?, ?, ?, ?)",[$intercompania, 0, 0, 0 ]);
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
            $solicitudCompra->estatus = 2;
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
        $prefijo = 'MC-' . strtoupper($codigoEntidad) . '-';

        // Buscar la última orden para ese código de entidad
        $ultimaOrden = SolicitudesCompra::macrotaller()
            ->where('folio', 'like', $prefijo . '%')
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
        $cotizacion = new SolicitudesCompraController;
        $folio = $cotizacion->generarFolioCo();
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
     * @param array $detalles Array de detalles, cada uno debe contener:
     *                         'cantidad', 'descripcion', 'observaciones', 'cat_unidades_medida_id'.
     * @param int $idSolicitud ID de la solicitud de compra a la que se asociarán los detalles.
     * @param array $files Array de archivos subidos, con claves como 'img_referencia_0', 'img_referencia_1', etc.
     */
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
}
