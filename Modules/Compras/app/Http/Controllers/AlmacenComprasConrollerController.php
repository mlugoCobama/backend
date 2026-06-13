<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Compras\Services\AlmacenService;
use Modules\Compras\Transformers\AlmacenComprasResource;
use Modules\Compras\Transformers\MovimientoAlmacenResource;
use Modules\Macro\Models\Almacen;

class AlmacenComprasConrollerController extends Controller
{
    protected $almacenService;

    public function __construct(
        AlmacenService $almacenService
    ){
        $this->almacenService = $almacenService;
    }

    /**
     * Rcupera los detalles de las compras de ti
     */
    public function index()
    {   
        $query = DB::table('com_almacen as ca')
        ->join('com_detalle_solicitud as cds', 'ca.com_detalle_solicitud_id', '=', 'cds.id')
        ->join('com_cat_unidades_medida as ccum', 'ccum.id', '=', 'cds.cat_unidades_medida_id')
        ->join('com_solicitudes_compra as csc', 'csc.id', '=', 'cds.solicitudes_compra_id')
        ->join('com_catalogo_sistemas_auto as ccsa', 'ccsa.id', '=', 'csc.com_cat_sistemas_auto_id')
        ->where('cds.confirmado', 1)
        ->where('csc.tipo', 3)
        ->where('ca.existencia','>', 0)
        ->select([
            'ca.id',
            'csc.id as solicitud_id',
            'csc.folio',
            'csc.empresa',
            'csc.usuario_destino',
            'ca.fecha_actualizacion as fecha',
            'csc.com_cat_sistemas_auto_id',
            'ccsa.sistema as categoria',
            'cds.id as detalle_id',
            // 'cds.cantidad',
            'ca.existencia as cantidad',
            'ccum.nombre as unidad',
            'cds.descripcion',
            'cds.observaciones',
            'ccum.nombre as unidad',
            'cds.estatus_almacen'
            
        ])
        ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Compras de recursos tecnologico recuperadas correctamete',
                'data' => AlmacenComprasResource::collection($query),
            ]);
    }


    public function create()
    {
        return view('compras::create');
    }


    public function store(Request $request)
    {
        
        $data = $request->all();
        $tecnicoAsignado = $data['tecnico'];

        $movimientos = $data['materiales'];

        $user = $request->user();
        $userId = $user->id;
        try {
        DB::beginTransaction();
            $entrega =  $this->almacenService->storeEntregaTecnico( $userId ,$tecnicoAsignado);
            foreach ($movimientos as $movimiento) {
            $this->almacenService->storeMovimientoAlmacen($movimiento,$userId,$tecnicoAsignado, $entrega->id);
            $this->almacenService->actualizarExistencia($movimiento['id'], $movimiento['cantidad'], -1);
        }
        DB::commit();
        } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Error al generar movimientos',
            'error' => $e->getMessage(),
            'status' => 'error',
        ], 500);
    }
    
        return response()->json([
            'data' => $data,
            'message' => 'Movimientos generados correctamente',
            'status' => 'success',
        ]);
    }

    public function show($id)
    {
        return view('compras::show');
    }


    public function edit($id)
    {
        return view('compras::edit');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function altaAlmacem(){

    }

    public function calcularExistencia(){

    }

    public function actualizarExistencia(){

    }

    public function recuperarMovimientosSolicitud(){

    }

    public function recuperarAlmacenEmpresa(){

    }

    public function getAlmacenDisponible( $tipo ){
        $almacen = DB::table('com_almacen as ca')
        ->join('com_detalle_solicitud as cds', 'ca.com_detalle_solicitud_id', '=', 'cds.id')
        ->join('com_cat_unidades_medida as ccum', 'ccum.id', '=', 'cds.cat_unidades_medida_id')
        ->join('com_solicitudes_compra as csc', 'csc.id', '=', 'cds.solicitudes_compra_id')
        ->join('com_catalogo_sistemas_auto as ccsa', 'ccsa.id', '=', 'csc.com_cat_sistemas_auto_id')
        ->where('cds.confirmado', 1)
        ->where('csc.tipo', $tipo)
        ->where('ca.existencia','>', 0)
        ->select([
            'ca.id',
            'csc.empresa',
            'csc.usuario_destino',
            'ca.fecha_actualizacion',
            'ccsa.sistema as categoria',
            'ca.existencia',
            'ccum.nombre as unidad',
            'cds.descripcion',
            'cds.observaciones'
        ])
        ->get();

        return  response()->json([
            'status' => 'success',
            'data' => $almacen,
            'message' => "Existencias recuperadas correctamente"
        ]);
    }

    public function getSalidas(){
        $almacen = DB::table('com_movimientos_almacen as cma')
                    ->select(
                        'cma.fecha as fecha_movimiento',
                        'cma.tipo as tipo_movimiento',
                        'cma.cantidad',
                        'cds.descripcion',
                        'ca.id as id_almacen',
                        'cma.id_usuario as usuario_entrega',
                        'cma.id_usuario_entrega as usuario_recibe'
                    )
                    ->join('com_almacen as ca', 'cma.com_almacen_id', '=', 'ca.id')
                    ->join('com_detalle_solicitud as cds', 'ca.com_detalle_solicitud_id', '=', 'cds.id')
                    ->get();


        return  response()->json([
            'status' => 'success',
            'data' => MovimientoAlmacenResource::collection($almacen),
            'message' => "Movimientos recuperadas correctamente"
        ]);
    }

    function generarNumeroControl($intercompania, $tipoCompra)
    {

        $prefijo = $intercompania.'-'.$tipoCompra.'-';
        $ultimo = Almacen::where('codigo_producto','LIKE', $prefijo.'%')->orderBy('id', 'desc')->first('codigo_producto');
        if ($ultimo) {
            $ultimoNro = $ultimo->codigo_producto;
            $numero = intval(substr($ultimoNro, strlen($prefijo))) + 1;
        } else {
            $numero = 1;
        }

        $nuevoNro = $prefijo . str_pad($numero, 5, '0', STR_PAD_LEFT);
        return $nuevoNro;
    }

}
