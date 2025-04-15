<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//Models
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\Proveedores;
use Modules\Compras\Models\DetallesCotizacion;
//Transformers

//Utilities
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Modules\Compras\Models\SolicitudesCompra;

use function Laravel\Prompts\error;

class CotizacionesController extends Controller
{
    /* *****************************************
     * Función quu genera folios consecutivos
     * *****************************************/
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
        return response()->json(['nuevoFolio' => $nuevoFolio]);
    }

    /* ***********************************************
     * Función para recuperar archivos del servidor  
     * **********************************************/
    public function getFile($id, $file)
    {
        $path = storage_path("app/cotizaciones/$id/$file");
        if (!File::exists($path)) {
            abort(404);
        }
        $fileContent = File::get($path);

        $type = File::mimeType($path);
        return response($fileContent, 200)->header("Content-Type", $type);

        // Convertir en base 64
        // $binaryContent = base64_encode($fileContent);
        // return response($binaryContent, 200)->header("Content-Type", 'application/octet-stream');
    }

    public function index()
    {
        return view('compras::index');
    }
    public function create()
    {
        return view('compras::create');
    }

    /*****************************************************
     * Guarda los importes unitarios de la cotización.
     * Si tiene archivos los guarda en la base de datos.
     * ****************************************************/
    public function store(Request $request)
    {
        $data = $request->all();


        $validacion = Validator::make($data, [
            'precios' => 'required|array|min:1',
            'precios.*' => 'array|min:1',
            'precios.*.*' => 'required|numeric|min:0.00',

            'files' => 'required|array',
            'files.*' => 'file|mimes:pdf|max:2048'
        ]);

        if ($validacion->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Datos no validos o incompletos',
                'errors' => $validacion->errors()
            ]);
        }

        // $tamPrcios = count($data['precios']);
        // $tamFiles = $request->hasFile('files') ? count($request->file('files')) : 0;
        // if($tamPrcios !== $tamFiles){
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Faltan precios o archivos',
        //         'errors' => []
        //     ]);
        // }

        try {
            DB::beginTransaction();

            foreach ($data['precios'] as $detalleId => $proveedores) {
                foreach ($proveedores as $proveedorId => $precio) {

                    //validar que la relación sea valida

                    DetallesCotizacion::create([
                        'detalle_solicitud_id' => $detalleId,
                        'cotizaciones_proveedores_proveedores_id' => $proveedorId,
                        'importe_unitario' => $precio,
                    ]);
                }
            }
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $cotizacionProveedorId => $file) {
                    $cotizacionProveedor = CotizacionesProveedores::find($cotizacionProveedorId);

                    if ($cotizacionProveedor) {
                        $folderPath = 'cotizaciones/' . $cotizacionProveedor->cotizaciones_id;
                        $fileName = $cotizacionProveedor->proveedores_id . '.' . $file->getClientOriginalExtension();

                        $path = $file->storeAs($folderPath, $fileName);

                        $cotizacionProveedor->update(['ruta' => $path]);
                    } else {
                        throw new Exception("Cotizacion-Proveedor con ID {$cotizacionProveedorId} no encontrada");
                    }
                }
            }
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
                'message' => 'Error al guardar los datos',
                'error' => $e->getMessage()
            ]);
        }
    }


    /**
     * Recupera información de la cotización con sus detalles
     */
    public function show($id)
    {
        //recupera datos de la cotización
        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)
            ->first(['id', 'consideraciones', 'solicitudes_compra_id']);

        // Recupera los detalles de las cotizaciones-proveedores asociadas a la cotización
        if ($cotizacion) {
            $proveedores = CotizacionesProveedores::where('cotizaciones_id', $cotizacion->id)
                ->get(['id', 'proveedores_id', 'cotizaciones_id', 'ruta', 'seleccionado']);
            $data = [];
            foreach ($proveedores as $proveedor) {
                $proveedorId = $proveedor->id;

                $detalles = DetallesCotizacion::where('cotizaciones_proveedores_proveedores_id', $proveedorId)
                    ->get(['id', 'importe_unitario', 'detalle_solicitud_id', 'cotizaciones_proveedores_proveedores_id']);

                $nombreProveedor = Proveedores::where('id', $proveedor->proveedores_id)->get(['id', 'nombre', 'correo']);
                $proveedor->proveedores_id = $nombreProveedor;
                $proveedor['detalles'] = $detalles;

                $data[] = $proveedor;
            }

            // Regreso el objeto data con los detalles y 
            //data cotización con la información de la cotización.
            return response()->json([
                'status' => 'success',
                'data' => $data,
                'dataCotizacion' => $cotizacion
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Cotización no encontrada',
                'data' => []
            ]);
        }
    }

    public function edit($id)
    {
        return view('compras::edit');
    }

    /**
     * Actualiza el estatus de la cotización seleccionada
     * Actualiza el status de la solicitud de compra
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $idSc = $data['0'];
        CotizacionesProveedores::where('id', $id)->update(['seleccionado' => 1]);
        SolicitudesCompra::where('id', $idSc)->update(['estatus' => 3]);
        return response()->json([
            'status' => 'success',
            'message' => 'Se ha actualizado correctamente',
            'data' => ''
        ]);
    }


    public function destroy($id)
    {
        //
    }
}
