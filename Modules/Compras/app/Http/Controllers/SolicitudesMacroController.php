<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Http\Requests\StoreSolicitudMacroRequest;
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Transformers\SolicitudesMacroResource;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Models\OrdenTrabajo;
use App\Enums\EstatusSolicitud;
use Modules\Compras\Transformers\AutotanqueResource;
use Modules\Compras\Transformers\UsersResource;

class SolicitudesMacroController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(int $intercompania)
    {
        if( $intercompania === 333){
            $data = SolicitudesMacroResource::collection(DB::select("call SistemaTickets.SP_GetSolicitudesMacro()"));
        }else
        {
            $data = SolicitudesMacroResource::collection(DB::select("call SistemaTickets.SP_GetSolicitudesMacroGasera($intercompania)"));
        }
        
        // $data = SolicitudesMacroResource::collection((SolicitudesCompra::macrotaller()->active()->orderBy('fecha', 'desc')->get()));
        return response()->json([
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

            $idSolicitud = $this->storeSolicitudCompra($data);
            $this->storeDetalleSolicitudCompra($data['detalles'], $idSolicitud, $files);
            $this->storeOrdenTrabajo($idSolicitud, $data);
            //TODO MODIFICAR ESTO PARA QUE SE ENVIÉ EL CORREO
            //$correos = $this->getGerente($data['empresa']);
            //$this->sendSolicitudAutorizacion($idSolicitud, $correos);
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
        $dataSolicitud->save();
        return $dataSolicitud->id;
    }

    /**
   * Almacena la orden de trabajo  en la base de datos
   * 
   * @param array $data debe contener 
   *                     "orden_trabajo":string, "usuario_destino":int
   * @param int $idSolicitud de la solicitud de compra
   */
    private function storeOrdenTrabajo($idSolicitud, $data){
        $dataOrdenTrabajo = new OrdenTrabajo();
        $dataOrdenTrabajo->orden_trabajo = $data["orden_trabajo"];
        $dataOrdenTrabajo->com_datos_vehiculo_id = $data["usuario_destino"];
        $dataOrdenTrabajo->com_solicitudes_compra_id = $idSolicitud;
        $dataOrdenTrabajo->save();
    }


    /**
     * Almacena los detalles de una solicitud de compra en la base de datos.
     *
     * @param array $detalles Array de detalles, cada uno debe contener:
     *                         'cantidad', 'descripcion', 'observaciones', 'cat_unidades_medida_id'.
     * @param int $idSolicitud ID de la solicitud de compra a la que se asociarán los detalles.
     * @param array $files Array de archivos subidos, con claves como 'img_referencia_0', 'img_referencia_1', etc.
     */
    private function storeDetalleSolicitudCompra($detalles, $idSolicitud, $files)
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
}
