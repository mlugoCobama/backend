<?php

namespace Modules\Compras\Http\Controllers;


use App\Http\Controllers\Controller;
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
        // return response()->json(['nuevoFolio' => $nuevoFolio]);
        return  $nuevoFolio;
    }

    /** ************************************************************
     * Recupera todos los registros de la base de datos
     **************************************************************/
    public function index()
    {
        //Catalogo de estados
        $solictado = 1;
        $enCotizacion = 2;
        $enOrdenCompra = 3;
        $autorizada = 4;
        $cancelada = 5;
        $enSurtido = 6;
        $pagada = 7;

        //  return SolicitudesComprasResource::collection((SolicitudesCompra::active()->orderBy('fecha', 'desc')->get()));
        $data = (SolicitudesCompra::active()->orderBy('fecha', 'desc')
            ->get([
                'id',
                'folio',
                'usuario_destino',
                'motivo',
                'fecha',
                'users_id',
                'usuario_solicita',
                'estatus',
            ]));

        foreach ($data as $item) {
            $user = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $item->usuario_destino . ')');
            $usuarioSolicita = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $item->usuario_solicita . ')');
            $item["usuario_solicita"] = '' . $usuarioSolicita[0]->firstname . ' ' . $usuarioSolicita[0]->realname . '' ?? 'No asignado';
            $item["usuario_destino"] = '' . $user[0]->firstname . ' ' . $user[0]->realname . '';
            $item["empresa"] = $user[0]->empresa;
            switch ($item->estatus) {
                case $solictado:
                    $item["estado"] = "SOLICITADO";
                    $item["claseEstado"] = "bg-primary";
                    break;
                case $enCotizacion:
                    $item["estado"] = "EN COTIZACIÓN";
                    $item["claseEstado"] = "bg-info";
                    break;
                case $enOrdenCompra:
                    $item["estado"] = "ORDEN DE COMPRA";
                    $item["claseEstado"] = "bg-warning";
                    break;
                case $autorizada:
                    $item["estado"] = "AUTORIZADA";
                    $item["claseEstado"] = "badge-soft-success";
                    break;
                case $cancelada:
                    $item["estado"] = "CANCELADA";
                    $item["claseEstado"] = "bg-danger";
                    break;
                case $enSurtido:
                    $item["estado"] = "EN SURTIDO";
                    $item["claseEstado"] = "badge-soft-warning";
                    break;
                case $pagada:
                    $item["estado"] = "PAGADA";
                    $item["claseEstado"] = "bg-success";
                    break;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Consulta generada correctamente',
            'data' => $data
            // 'data' => new SolicitudesComprasResource($solicitudCompra)
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
    public function store(Request $request)
    {
        $data = json_decode($request->input('data'), true);
        $files =  $request->allFiles();

        //  $validador = Validator::make($data, [
            
        //      'usuario_solicita' => 'required|integer',
        //      'usuario_destino' => 'required|integer',
        //      'motivo' => 'required|string',
        //      'users_id' => 'required||integer',
        //      'detalles' => 'required|array|min:1',
        //      'detalles.*.cantidad' => 'required|numeric|min:1',
        //      'detalles.*.descripcion' => 'required|string',
        //      'detalles.*.observaciones' => 'nullable|string',
        //      'detalles.*.cat_unidades_medida_id' => 'required|integer',
        //  ]);

        //  //validación archivos
        //  foreach ($files as $key => $file) {
        //      if (strpos($key, 'img_referencia_') === 0) {
        //          $validador->after(function ($validador) use ($file, $key) {
        //              if (!$file->isValid() || !in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
        //                  $validador->errors()->add($key, 'El archivo debe ser una imagen valida');
        //              }
        //              if ($file->getSize() > 5 * 1024 * 1024) {
        //                  $validador->errors()->add($key, 'El archivo no puede superar los 5MB');
        //              }
        //          });
        //      }
        //  }

        //  if ($validador->fails()) {
        //      return response()->json([
        //          'status' => 'error',
        //          'message' => 'Datos no validos',
        //          'errors' => $validador->errors()
        //      ]);
        //  }

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
        $dataSolicitud->users_id = $data["users_id"];
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

    /** *******************************************************************
     * Recupera las solicitud de compra por id
     *********************************************************************/
    public function getSolicitud($id)
    {
        //Catalogo de estados
        $solictado = 1;
        $enCotizacion = 2;
        $enOrdenCompra = 3;
        $autorizada = 4;
        $cancelada = 5;
        $enSurtido = 6;
        $pagada = 7;

        $data = SolicitudesCompra::where("id", $id)->first();

        $user = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $data->usuario_destino . ')');
        $usuarioSolicita = DB::connection('intranet')->select('call SOPORTEZM.SP_GetUsuarioId(' . $data->usuario_solicita . ')');
        $data["usuario_solicita"] = '' . $usuarioSolicita[0]->firstname . ' ' . $usuarioSolicita[0]->realname . '' ?? 'No asignado';
        $data["usuario_destino"] = '' . $user[0]->firstname . ' ' . $user[0]->realname . '';
        $data["empresa"] = $user[0]->empresa;
        switch ($data->estatus) {
            case $solictado:
                $data["estado"] = "SOLICITADO";
                $data["claseEstado"] = "bg-primary";
                break;
            case $enCotizacion:
                $data["estado"] = "EN COTIZACIÓN";
                $data["claseEstado"] = "bg-info";
                break;
            case $enOrdenCompra:
                $data["estado"] = "ORDEN DE COMPRA";
                $data["claseEstado"] = "bg-warning";
                break;
            case $autorizada:
                $data["estado"] = "AUTORIZADA";
                $data["claseEstado"] = "badge-soft-success";
                break;
            case $cancelada:
                $data["estado"] = "CANCELADA";
                $data["claseEstado"] = "bg-danger";
                break;
            case $enSurtido:
                $data["estado"] = "EN SURTIDO";
                $data["claseEstado"] = "badge-soft-warning";
                break;
            case $pagada:
                $data["estado"] = "PAGADA";
                $data["claseEstado"] = "bg-success";
                break;
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Consulta generada correctamente',
            'data' => $data
        ]);
    }

    public function edit($id)
    {
        return view('compras::edit');
    }

    /*
     * Esto no se ocupa
     */
    public function update(Request $request, $id)
    {
        SolicitudesCompra::where('id', $id)
            ->update([
                'folio' => $request->folio,
                'usuario_solicita' => $request->usuario_solicita,
                'usuario_destino' => $request->usuario_destino,
                'motivo' => $request->motivo,
                'fecha' => $request->fecha,
                'users_id' => $request->users_id
            ]);
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
            'estatus' => 5
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
    public function enviarSolicitudCotizacion(Request $request)
    {
        $data = $request->all();

        // $validacion = Validator::make($data, [
        //     'consideraciones' => 'nullable|string',
        //     'proveedor1' => 'required|integer',
        //     'proveedor2' => 'required|integer',
        //     'proveedor3' => 'required|integer',
        //     'solicitudes_compra_id' => 'required|integer',
        // ]);

        // if ($validacion->fails()) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Datos no validos o incompletos',
        //         'errors' => $validacion->errors()
        //     ]);
        // }

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

                // Actualiza el estatus de la Solicitud a 2
                SolicitudesCompra::where('id', $idSolicitudC)->update(['estatus' => 2]);

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
