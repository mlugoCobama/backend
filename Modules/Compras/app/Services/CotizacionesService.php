<?php

namespace Modules\Compras\Services;

use App\Enums\EstatusSolicitud;
use App\Mail\SolicitudCotizacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\DatosVehiculo;
use Modules\Compras\Models\DetalleSolicitud;
use Modules\Compras\Models\ProveedorContacto;
use Modules\Compras\Models\Proveedores;
use Modules\Compras\Models\SolicitudesCompra;

class CotizacionesService
{

    public function  existCotizacion($id)
    {
        $registro = Cotizaciones::find($id);

        if ($registro) {
            return $registro;
        }

        return false;
    }

    /**
     * Limpia la selección de cotización preveedor-ligada a una cotizacion
     */
    public function desmarcarCotizacionSeleccionada($cotizacionId)
    {
        CotizacionesProveedores::where('cotizaciones_id', $cotizacionId)
            ->update(['seleccionado' => 0]);
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
     * Crea una una nueva cotización
     */
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
            $datacotProv->proveedores_id = $proveedor['proveedor_id'];
            $datacotProv->cotizaciones_id = $idCotizacion;
            $datacotProv->contacto_id = $proveedor['contacto_id'];
            $datacotProv->save();

            $idsCotProv[] = $datacotProv->id;
        }

        return $idsCotProv;
    }

    /**
     * Valida que el proveedor tenga un correo asignado
     */
    public function validateProveedoresConCorreo($proveedores)
    {
        $sinCorreo = $proveedores->filter(fn($p) => empty($p->correo));
        return $sinCorreo->isNotEmpty()
            ? 'Algunos proveedores no tienen correo: ' . $sinCorreo->pluck('nombre')->implode(', ')
            : null;
    }

    /**
     * Valida que los contactos tenga un correo asignado
     */
    public function validateContactosConCorreo($items, $proveedores)
    {
        $sinCorreo = $items->filter(function ($item) use ($proveedores) {
            if (!$item['contacto_id']) return false;
            $contacto = $proveedores[$item['proveedor_id']]->contactos
                ->firstWhere('id', $item['contacto_id']);
            return empty($contacto?->correo);
        });
        return $sinCorreo->isNotEmpty()
            ? 'Algunos contactos no tienen correo asignado'
            : null;
    }

    /** ***************************************************************************
     * Función que  envía el correo de solicitud de cotización a los proveedores
     ****************************************************************************/
    public function enviaCorreoProveedores($proveedores, $data)
    {
        $solicitudCompra =  SolicitudesCompra::find($data['solicitudes_compra_id']);
        if ($solicitudCompra) {
            $unidadDestino = $solicitudCompra->tipo == 2 ? DatosVehiculo::find($solicitudCompra->usuario_destino) : null;
            $detalles = DetalleSolicitud::with(['unidadMedida'])->where("solicitudes_compra_id", $data['solicitudes_compra_id'])
                ->confirmadas()
                ->when(($solicitudCompra->tipo == 2) && ($solicitudCompra->usuario_destino == 602), function ($query) {
                    $query->with('DetalleAutotanque.DatosVehiculo');
                })
                ->get();

            $data['proveedores'] = $proveedores->toArray();
            $data['solicitudCompra'] = $solicitudCompra;
            $data['unidadDestino'] = $unidadDestino;
            $data['detalles'] = $detalles;


            foreach ($proveedores as $proveedor) {
                $correo = '';

                if (!empty($proveedor['contacto_id'])) {
                    $correo = ProveedorContacto::find($proveedor['contacto_id']);
                } else {
                    $correo = Proveedores::find($proveedor['proveedor_id']);
                }

                if (!empty($correo->correo)) {
                    try {
                        // Notification::route('mail', $proveedor->correo)
                        //     ->notify(new SolicitudCotizacionNotification($data));
                        Mail::to($correo->correo)->send(new SolicitudCotizacion($data));
                    } catch (\Exception $e) {
                        Log::error("Error al enviar correo a proveedor {$proveedor->id}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Marca como seleccionada la cotizacion-proveedor con el id otorgado
     */
    public function  cotizacionProveedorSeleccionada($idCotizacionProveedor)
    {
        $cotizacionPrveedor =  CotizacionesProveedores::find($idCotizacionProveedor);
        if ($cotizacionPrveedor) {
            $cotizacionPrveedor->seleccionado = 1;
            $cotizacionPrveedor->save();
        }
    }

    public function validarCotizacion($id)
    {
        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)->first();

        if (!empty($cotizacion)) {
            $cotizacionesDisponibles = CotizacionesProveedores::where('cotizaciones_id', $cotizacion->id)->get();
            $solicitud = SolicitudesCompra::findOrFail($id);
            if ($solicitud->auto_admin === 1 && $solicitud->auto_gg === 1) {
                $solicitud->estatus = EstatusSolicitud::EN_COTIZACION;
                $solicitud->save();
            }

            if ($cotizacionesDisponibles->isNotEmpty()) {
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

    public function enviarSolicitudCotizacion($data)
    {
        // Obtener los proveedores completos desde la base de datos
        $items = collect($data['proveedores']);
        $idSolicitudC = $data['solicitudes_compra_id'];
        $proveedoresIds = $items->pluck('proveedor_id')->unique();

        $proveedores = Proveedores::whereIn('id', $proveedoresIds)
            ->with('contactos')
            ->get()
            ->keyBy('id');
        // Verificar que todos los proveedores tengan correo asignado
        if ($error = $this->validateProveedoresConCorreo($proveedores)) {
            return $this->errorResponse($error);
        }

        if ($error = $this->validateContactosConCorreo($items, $proveedores)) {
            return $this->errorResponse($error);
        }

        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $data['solicitudes_compra_id'])->first();
        if ($cotizacion) {
            $idCotizacion = $cotizacion->id;
        } else {
            $idCotizacion = $this->storeCotizacion($data);
            $solicitud = SolicitudesCompra::find($idSolicitudC);
            $solicitud->estatus = EstatusSolicitud::EN_COTIZACION;
            $solicitud->save();
        }
        $this->storeCotizacionProveedores($items, $idCotizacion);
        //Queue para despachar el correo EnviarCorreoSolicitudCotizacion::dispatch($data)
        $this->enviaCorreoProveedores($items, $data);

        return [
            'cotizacion_id' => $idCotizacion,
            'proveedores_count' => $proveedores->count(),
            'solicitud_id' => $idSolicitudC
        ];
    }

    private function errorResponse($message, $error = null, $data = [])
    {
        DB::rollBack();
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'error'   => $error,
            'data'    => $data
        ], 422);
    }


}
