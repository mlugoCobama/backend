<?php

namespace Modules\Compras\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Enums\EstatusSolicitud;
// use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// use Illuminate\Http\Response;
// use League\CommonMark\Extension\Attributes\Node\Attributes;
//Models
use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Transformers\DetalleSolicitudCompraResource;
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\DetallesCotizacion;

//Transformers
use Modules\Compras\Transformers\SolicitudesComprasResource;
//Utilities
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use DateTime;
use DateTimeZone;
// Mailiables
use App\Mail\SolicitudCotizacion;
use App\Notifications\SolicitudCotizacionNotification;
// Jobs
use App\Jobs\EnviarCorreoSolicitudCotizacion;
use App\Models\User;
use Modules\Compras\Models\Proveedores;

use Modules\Compras\Http\Requests\StoreSolicitudCompraRequest;
use Modules\Compras\Http\Requests\SendSolicitudCotizacionRequest;

class SolicitudesCompraController extends Controller
{

    /** *********************************************************** 
    * Genera un nuevo folio consecutivo en base al ultimo folio
    *************************************************************/
    public function generarFolioSc()
    {
        $ultimaOrden = SolicitudesCompra::orderBy('id', 'desc')->first('folio');
        if ($ultimaOrden) {
            $ultimoFolio = $ultimaOrden->folio;
            $numero = intval(substr($ultimoFolio, 3)) + 1;
        } else {
            $numero = 1;
        }
        $nuevoFolio = 'SC-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
        return  $nuevoFolio;
    }

    /** ************************************************************
     * Recupera todos los registros de la base de datos
     **************************************************************/
    public function index()
    {
        $data =  SolicitudesComprasResource::collection((SolicitudesCompra::active()->orderBy('fecha', 'desc')->get()));

        return response()->json([
            'status' => 'success',
            'message' => 'Consulta generada correctamente',
            'data' => $data
        ]);
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

    public function create()
    {
        return view('compras::create');
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

                $idSolicitud = $this->storeSolicitudCompra($data);

                $this->storeDetalleSolicitudCompra($data['detalles'], $idSolicitud, $files);

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

    /** ********************************************************************
    *Primero genero una solicitud de compra
    *Después almaceno los detalles de la solicitud
    **********************************************************************/
    private function storeSolicitudCompra($data)
    {
        $dataSolicitud = new SolicitudesCompra();
        $dataSolicitud->folio = $this->generarFolioSc();
        $dataSolicitud->usuario_solicita = $data["usuario_solicita"];
        $dataSolicitud->usuario_destino = $data["usuario_destino"];
        $dataSolicitud->motivo = $data["motivo"];
        $dataSolicitud->fecha = $this->getFecha() ?? now();
        $dataSolicitud->c_c = $data["c_c"];
        $dataSolicitud->save();
        return $dataSolicitud->id;
    }

    /** ***************************************************************************
     * Amacena los detalles de la solicitud
     *****************************************************************************/
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

    /** *******************************************************************
     * Recupera los detalles de la solicitud
     *********************************************************************/
    public function show($id)
    {
        return DetalleSolicitudCompraResource::collection((DetalleSolicitud::where('solicitudes_compra_id', $id)->get()));
    }

    public function edit($id)
    {
        return view('compras::edit');
    }

    /**
     * Actualiza los estatus de la solicitud de compra
     * auto_gg, auto_admin y estatus
     */
    public function update(Request $request, $id)
    {
        SolicitudesCompra::where ('id', $id)->update(
           [ "$request->campo" => $request->value]
        );

        return response()->json([
             'status' => 'success',
             'message' => 'Se ha actualizado correctamente',
             'data' => ''
         ]);
    }

    /** ************************************************************
     * Actualiza el estatus a cancelado
     **************************************************************/
    public function destroy($id)
    {

        $solicitudCompra = SolicitudesCompra::where('id', $id);

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

                    //Adecuación nuevo front
                    $idsProv = [$data['proveedor1'], $data['proveedor2'], $data['proveedor3']];
                    $data['proveedores'] = [];
                    foreach ($idsProv as $id) {
                        $proveedor = Proveedores::where("id", $id)->first();
                        $data['proveedores'][] =  $proveedor;
                    }

                    $data['detalles'] =  DetalleSolicitud::where("solicitudes_compra_id", $data['solicitudes_compra_id'])->get();

                // Almacenar la relación entre cotización y proveedores
                $this->storeCotizacionProveedores($data['proveedores'], $idCotizacion);
                
                //Queue para despachar el correo
                //!Habiltar para que se envíen los correos EnviarCorreoSolicitudCotizacion::dispatch($data); 

                /** *****************************************************************************************
                 * !Habiltar para que se envíen los correos 
                 * 
                 *******************************************************************************************/ 
                //$this->enviaCorreoProveedores($data['proveedores'], $data);
                $idSolicitudC = $data['solicitudes_compra_id'];

                // Actualiza el estatus de la Solicitud a COTIZACION
                SolicitudesCompra::where('id', $idSolicitudC)->update(['estatus' => EstatusSolicitud::EN_COTIZACION]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Correos enviados correctamente',
                // 'data' => []
                'data' => $data
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Algo fallo',
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

    /** ***********************************************************************
     * Función que genera la fecha actual tiempo de Mexico
     ************************************************************************/
    public function getFecha()
    {
        $fecha = new DateTime('now', new DateTimeZone('America/Mexico_City'));
        $fecha = $fecha->format('Y-m-d H:i:s');
        return $fecha;
    }

     /** ***********************************************************************
     * Almacena la cotización y devuelve el id del registro creado
     ************************************************************************/
    public function storeCotizacion($data)
    {
        $dataCotizacion = new Cotizaciones();
        $dataCotizacion->folio = $this->generarFolioCo();
        $dataCotizacion->fecha = $this->getFecha() ?? now();
        $dataCotizacion->consideraciones = $data["consideraciones"];
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
            $datacotProv->proveedores_id = $proveedor['id'];
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
            //Mail::to($correo)->send(new SolicitudCotizacion($data));
            Notification::route('mail', $proveedor['correo'])
                ->notify(new SolicitudCotizacionNotification($data));
        }
    }

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
}
