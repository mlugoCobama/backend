<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\EstatusSolicitud;

//Models
use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\Proveedores;
use Modules\Compras\Models\DetallesCotizacion;
//Transformers

//Utilities
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

use Modules\Compras\Models\SolicitudesCompra;

class CotizacionesController extends Controller
{

    /** ***************************************************
     * Guarda los importes unitarios de la cotización.
     * Si tiene archivos los guarda en la base de datos.
     * ****************************************************/
    public function store(Request $request)
    {
        $data = $request->all();

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
                ->get(['id', 'proveedores_id', 'cotizaciones_id', 'ruta', 'seleccionado', 'autorizado']);
                
            $data = [];
            foreach ($proveedores as $proveedor) {
                $proveedorId = $proveedor->id;

                $detalles = DetallesCotizacion::where('cotizaciones_proveedores_proveedores_id', $proveedorId)->with('detalle_solicitud')
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


    /**
     * Actualiza el estatus de la cotización seleccionada
     * Actualiza el status de la solicitud de compra
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $idSc = $data['0'];
        CotizacionesProveedores::where('id', $id)->update(['seleccionado' => 1]);
        SolicitudesCompra::where('id', $idSc)->update(['estatus' => EstatusSolicitud::EN_ORDEN_COMPRA]);
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

    /**
    * Genera un nuevo folio consecutivo para cotizaciones.
    *
    * @return \Illuminate\Http\JsonResponse Devuelve el nuevo folio en formato JSON con la clave 'nuevoFolio'.
    */
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

    /**
    * Recupera un archivo almacenado en el servidor relacionado con una cotización específica.
    *
    * @param int $id El ID de la cotización asociada.
    * @param string $file El nombre del archivo que se desea recuperar.
    * @return \Illuminate\Http\Response El archivo como respuesta HTTP, incluyendo el tipo de contenido.
    */
    public function getFile($id, $file)
    {
        $path = storage_path("app/cotizaciones/$id/$file");
        if (!File::exists($path)) {
            abort(404);
        }

        $fileContent = File::get($path);

        $type = File::mimeType($path);
        return response($fileContent, 200)->header("Content-Type", $type);
    }


    public function autorizarCotizacion($idCotizacion){
        CotizacionesProveedores::where('id', $idCotizacion)->update(['autorizado' => 1]);
        return response()->json([
            'status' => 'Success',
            'message' => 'Actualizado con exito',
            'data' => []
        ]);
    }

    public function limpiarAutorizaciones($id){
        SolicitudesCompra::where('id', $id)->update([
            'auto_admin' => 0,
            'auto_gg' => 0,
        ]);
        
        return response()->json([
            'status' => 'Success',
            'message' => 'Actualizado con exito',
            'data' => []
        ]);
    }
}
