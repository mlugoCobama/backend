<?php

namespace Modules\ParqueVehicular\Http\Controllers;

use App\Enums\EstatusOrdenesServicio;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\ParqueVehicular\Services\EventosOsService;
use Modules\ParqueVehicular\Models\OrdenServicio;
use Modules\ParqueVehicular\Models\VehiculoProveedor;
use Modules\ParqueVehicular\Services\OrdenServicioCobService;
use Modules\ParqueVehicular\Transformers\OrdenServicioResource;
use Modules\ParqueVehicular\Transformers\ProveedorVehiculoResource;

class VehiculoProveedorController extends Controller
{

    public function __construct(
        protected EventosOsService $eventosService,
        protected OrdenServicioCobService $ordeServicioService) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
    $filtros = [
        'fecha_inicio'       => $request->input('fechaInicio') ?? $request->input('fecha_inicio') ?? '',
        'fecha_fin'          => $request->input('fechaFin')?? $request->input('fecha_fin') ?? '',
        'intercompania'      => $request->input('intercompania'),
        'com_proveedores_id' => $request->input('proveedor'),
        'estatus' => $request->input('estatus') ? explode(",", $request->input('estatus')) : '',
    ];

    //Si el usuario autenticado es un Proveedor, forzamos su ID de proveedor
    // if ($request->user()?->hasRole('proveedor')) {
    //     $filtros['com_proveedores_id'] = $request->user()->com_proveedores_id;
    // }

    // Si el usuario autenticado pertenece a una Empresa específica
    // if ($request->user()?->hasRole('empresa')) {
    //     $filtros['intercompania'] = $request->user()->intercompania;
    // }

    $query = OrdenServicio::active()
        ->filtrar($filtros)
        ->with(['proveedorAsignado.proveedor', 'vehiculo'])
        ->orderBy('fecha_inicio', 'DESC')
        ->get();

    return response()->json([
        'status'  => 'success',
        'data'    => ProveedorVehiculoResource::collection($query),
        'message' => 'Datos recuperados correctamente'
    ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('parquevehicular::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $vehiculoProveedor =  new OrdenServicio();
        $vehiculoProveedor->folio = $this->ordeServicioService->generarFolioOS('1');
        $vehiculoProveedor->com_datos_vehiculos_id = $request->vehiculoId;
        // $vehiculoProveedor->com_proveedores_id = $request->proveedorId;
        $vehiculoProveedor->intercompania = $request->empresaId;
        $vehiculoProveedor->observaciones = $request->observaciones;
        $vehiculoProveedor->descripcion_falla = $request->descripcion_falla;
        $vehiculoProveedor->tipo_mantenimiento = $request->tipoMantenimiento;
        $vehiculoProveedor->sistema_auto = $request->sistema;
        $vehiculoProveedor->fecha_inicio = now();
        $vehiculoProveedor->save();

        return response()->json([
            'status' => 'success',
            'data'=> $request,
            'message' => 'Datos guardados correctamente',
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $query = OrdenServicio::active()->with(
            'proveedorAsignado.proveedor',
            'proveedorAsignado.eventos',
            'proveedorAsignado.trabajos.partesTrabajo',
            'vehiculo',
            'proveedorAsignado.evidencias')->find($id);
        return response()->json(
            [   'status' => 'success',
                'data' => new OrdenServicioResource($query),
                // 'data' => ProveedorVehiculoResource::collection($query),
                'message' => 'Datos recuperados correctamente'
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('parquevehicular::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $ordenServicio = OrdenServicio::active()->find($id);
        if($ordenServicio){
            $ordenServicio->com_proveedores_id = $request->proveedor_id;
            $ordenServicio->estatus = EstatusOrdenesServicio::ASIGNADO;
            $ordenServicio->save();



            $ordenProveedor = new VehiculoProveedor();
            $ordenProveedor->com_proveedores_id = $request->proveedor_id;
            $ordenProveedor->pv_orden_servicio_id = $ordenServicio->id;
            $ordenProveedor->save();

            //Generar el primer evento
            $eventoEspAsignacion = $this->eventosService->inciarEvento($ordenProveedor->id, EstatusOrdenesServicio::ESP_ASIGNACION, $ordenServicio->fecha_inicio);
            $this->eventosService->finalizarEvento($eventoEspAsignacion->id, '');

            // Iniciamos el segundo evento asignado
            $this->eventosService->inciarEvento($ordenProveedor->id, EstatusOrdenesServicio::ASIGNADO);

            return response()->json([
                'status' => 'success',
                'data' => [],
                'message' => 'Proveedor asignado correctamente'
            ]);
        }

        return response()->json(
            [   'status' => 'success',
                'data' => [],
                'message' => 'Proveedor asignado correctamente'
            ]
        );

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }


 public function autorizarOrdenServicio(Request $request)
    {

        $idOrdenServicio = $request->idOrdenServicio;
        $idOrdenProv = $request->idOrdenProv;
        $accion = $request->accion;

        try {
            DB::transaction(function () use ($idOrdenServicio, $idOrdenProv, $accion) {
                $this->eventosService->finalizarUltimoEvento($idOrdenProv);
                $esAutorizacion = ($accion === 'autorizar');

                // Actualización de OrdenServicio
                $filasOrden = OrdenServicio::where('id', $idOrdenServicio)->update([
                    'estatus' => $esAutorizacion ? 5 : 1,
                ]);

                // actualización de VehiculoProveedor
                $datosProveedor = $esAutorizacion
                    ? ['autorizado' => 1]
                    : ['activo' => 0];

                $filasProv = VehiculoProveedor::where('id', $idOrdenProv)->update($datosProveedor);

                $evento = $esAutorizacion ? $this->eventosService->inciarEvento($idOrdenProv, EstatusOrdenesServicio::EN_PROCESO): null;

                // Validar si los registros realmente existían
                if ($filasOrden === 0 || $filasProv === 0) {
                    throw new \Exception('No se encontró alguna de las órdenes especificadas.');
                }
            });

            $mensaje = ($accion === 'autorizar')
                ? 'Orden de servicio autorizada correctamente.'
                : 'Orden de servicio rechazada correctamente.';

            return response()->json([
                'status' => 'success',
                'data' => [],
                'message' => $mensaje
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'data' => [],
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ]);
        }
    }

    public function finalizarOrden(Request $request){
        $idOrdenServicio = $request->idOrdenServicio;
        $idOrdenProv = $request->idOrdenProv;
        // $accion = $request->accion;
        try {
            DB::transaction(function () use ($idOrdenServicio, $idOrdenProv) {
                $this->eventosService->finalizarUltimoEvento($idOrdenProv);

                // Actualización de OrdenServicio
                $filasOrden = OrdenServicio::where('id', $idOrdenServicio)->update([
                    'estatus' => EstatusOrdenesServicio::FINALIZADA,
                ]);

                $finalizacion = $this->eventosService->inciarEvento($idOrdenProv, EstatusOrdenesServicio::FINALIZADA);
                $this->eventosService->finalizarEvento($finalizacion->id);
            });

            return response()->json([
                'status' => 'success',
                'data' => [],
                'message' => 'Orden finaleizada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'data' => [],
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ]);
        }


    }



}
