<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
// use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// use Illuminate\Http\Response;
// use League\CommonMark\Extension\Attributes\Node\Attributes;

use App\Mail\SolicitudCotizacion;
use Illuminate\Support\Facades\Mail;

use App\Notifications\SolicitudCotizacionNotification;
use Illuminate\Support\Facades\Notification;

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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SolicitudesComprasResource::collection((SolicitudesCompra::active()->get()));
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
        $idSolicitud = $this->storeSolicitudCompra($data); 
        $this->storeDetalleSolicitudCompra($data['detalles'], $idSolicitud, $request->allFiles());

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha guardado correctamente',
            'data' => []
            // 'data' => new SolicitudesComprasResource($solicitudCompra)
        ]);
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        SolicitudesCompra::where('id', $id)->update(['estatus' => 5]);
        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => ''
        ]);
    }


/*---------------------------------------------------------------------
*POSIBLE SOLUCIÓN
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
/**
* SOLUCIÓN PARA GUARDAR LA COTIZACIÓN
* Generar una función que ejecute lo siguiente
* Almacenar la cotización
* Almacenar la relación entre cotización y proveedores
* ?Almacenar la relación entre detalles y cotizacionProveedores
* Actualiza el estatus de la Solicitud a 2
*/

    public function enviarSolicitudCotizacion(Request $request)
    {
        $data = $request->all();
        $idCotizacion = $this->storeCotizacion($data);
        // $idDataCotProv = 
        $this->storeCotizacionProveedores($data, $idCotizacion);
        // $this->storeDetallesCotizacion($data, $idDataCotProv);
        //!Habiltar para que se envien los correos $this->enviaCorreoProveedores($data);

        $idSolicitudC = $data['solicitudes_compra_id'];
        SolicitudesCompra::where('id', $idSolicitudC)->update(['estatus' => 2]);

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
    public function storeCotizacionProveedores($data, $idCotizacion)
    {
        $proveedores = [$data['0'], $data['1'], $data['2']];
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
    public function enviaCorreoProveedores($data)
    {
        $proveedores = [$data['0'], $data['1'], $data['2']];
        foreach ($proveedores as $proveedor) {
            //Mail::to($correo)->send(new SolicitudCotizacion($data));
            Notification::route('mail', $proveedor['correo'])->notify(new SolicitudCotizacionNotification($data));
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
