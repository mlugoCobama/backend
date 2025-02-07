<?php

namespace Modules\Compras\Http\Controllers;

use App\Jobs\EnviarCorreoSolicitudCotizacion;
use App\Http\Controllers\Controller;
// use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// use Illuminate\Http\Response;
// use League\CommonMark\Extension\Attributes\Node\Attributes;


use App\Mail\SolicitudCotizacion;
use Illuminate\Support\Facades\Mail;

use App\Notifications\SolicitudCotizacionNotification;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * Modelos
 */

use Modules\Compras\Models\SolicitudesCompra;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Transformers\DetalleSolicitudCompraResource;
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\DetallesCotizacion;

/**
 * Resources
 */

use Modules\Compras\Transformers\SolicitudesComprasResource;


class SolicitudesCompraController extends Controller
{

    //Genera un nuevo folio consecutivo en base al ultimo folio
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
        return response()->json(['nuevoFolio' => $nuevoFolio]);
    }

    /**
     * Recupera todos los registros de la base de datos
     */
    public function index()
    {
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

        return response()->json([
            'status' => 'success',
            'message' => 'Consulta generada correctamente',
            'data' => $data
            // 'data' => new SolicitudesComprasResource($solicitudCompra)
        ]);
    }

    /**
     * Recupera todos los registros de la base de datos
     * *Con paginacion (30 registros por pagina)
     */
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
        // $solicitudCompra = SolicitudesCompra::create($request->all());
        // $idSolicitud = $this->storeSolicitudCompra($request);
        // $this->storeDetalleSolicitudCompra($request->detalles, $idSolicitud);

        $data = json_decode($request->input('data'), true);
        $files =  $request->allFiles();

        $validador = Validator::make($data, [
            'folio' => 'required|string|max:50',
            'usuario_solicita' => 'required|integer',
            'usuario_destino' => 'required|integer',
            'motivo' => 'required|string|max:50',
            'fecha' => 'required|string|max:50',
            'users_id' => 'required||integer',
            //validación de los detalles
            'detalles' => 'required|array|min:1',
            'detalles.*.cantidad' => 'required|numeric|min:1',
            'detalles.*.descripcion' => 'required|string|max:255',
            'detalles.*.observaciones' => 'nullable|string|max:255',
            'detalles.*.cat_unidades_medida_id' => 'required|integer',
        ]);

        //validación archivos
        foreach ($files as $key => $file) {
            if (strpos($key, 'img_referencia_') === 0) {
                $validador->after(function ($validador) use ($file, $key) {
                    if (!$file->isValid() || !in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
                        $validador->errors()->add($key, 'El archivo debe ser una imagen valida');
                    }
                    if ($file->getSize() > 5 * 1024 * 1024) {
                        $validador->errors()->add($key, 'El archivo no puede superar los 5MB');
                    }
                });
            }
        }

        if ($validador->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Datos no validos',
                'errors' => $validador->errors()
            ]);
        }

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

    /*---------------------------------------------------------------------
*Primero genero una solicitud de compra
*Después almaceno los detalles de la solicitud
*---------------------------------------------------------------------
*/

    private function storeSolicitudCompra($data)
    {
        $dataSolicitud = new SolicitudesCompra();
        $dataSolicitud->folio = $data["folio"];
        // $dataSolicitud-> folio = $data["folio"] ;
        $dataSolicitud->usuario_solicita = $data["usuario_solicita"];
        $dataSolicitud->usuario_destino = $data["usuario_destino"];
        $dataSolicitud->motivo = $data["motivo"];
        $dataSolicitud->fecha = $data["fecha"];
        $dataSolicitud->users_id = $data["users_id"];
        $dataSolicitud->save();
        return $dataSolicitud->id;
    }

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

    /*---------------------------------------------------------------------
    *POSIBLE SOLUCIÓN
    *Obtener todos lo datos de la consulta
    *Dividir en dos arreglos distintos la solicitud y el detalle 
    *Finalmente mostramos la solicitud con los detalles
    *---------------------------------------------------------------------
    */
    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return DetalleSolicitudCompraResource::collection((DetalleSolicitud::where('solicitudes_compra_id', $id)->get()));
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

    /**
     * Actualiza el estatus a cancelado
     */
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

    /**
     * SOLUCIÓN PARA GUARDAR LA COTIZACIÓN
     * Generar una función que ejecute lo siguiente
     * Almacenar la cotización
     * Almacenar la relación entre cotización y proveedores
     * ?Almacenar la relación entre detalles y cotizacionProveedores
     * Actualiza el estatus de la Solicitud a 2
     */
    //Queue para despachar el correo
    //!Habiltar para que se envíen los correos EnviarCorreoSolicitudCotizacion::dispatch($data); 
    // 

    public function enviarSolicitudCotizacion(Request $request)
    {
        $data = $request->all();

        $validacion = Validator::make($data, [
            'folioCo' => 'required|string|max:50',
            'fecha' => 'required|date',
            'consideraciones' => 'nullable|string|max:150',
            'solicitudes_compra_id' => 'required|integer',
            'proveedores' => 'required|array|min:1',
            'proveedores.*.id' => 'required|integer',
            'proveedores.*.correo' => 'required|email|max:50|distinct',
            'folioCo' => 'required|string|max:50',
        ]);

        if ($validacion->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Datos no validos o incompletos',
                'errors' => $validacion->errors()
            ]);
        }

        try {
            
            DB::beginTransaction();
            $idCotizacion = $this->storeCotizacion($data);
            $this->storeCotizacionProveedores($data['proveedores'], $idCotizacion);
            //Queue para despachar el correo
            //!Habiltar para que se envíen los correos EnviarCorreoSolicitudCotizacion::dispatch($data); 
            // 
            // !Habiltar para que se envíen los correos $this->enviaCorreoProveedores($data['proveedores'], $data);

            $idSolicitudC = $data['solicitudes_compra_id'];
            SolicitudesCompra::where('id', $idSolicitudC)->update(['estatus' => 2]);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Correos enviados correctamente',
                'data' => []
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Algo fallo',
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Correos enviados correctamente'
        ]);
    }

    public function storeCotizacion($data)
    {
        $dataCotizacion = new Cotizaciones();
        $dataCotizacion->folio = $data["folioCo"];
        $dataCotizacion->fecha = $data["fecha"] ?? now();
        $dataCotizacion->consideraciones = $data["consideraciones"];
        $dataCotizacion->solicitudes_compra_id = $data["solicitudes_compra_id"];

        $dataCotizacion->save();
        return $dataCotizacion->id;
    }

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
