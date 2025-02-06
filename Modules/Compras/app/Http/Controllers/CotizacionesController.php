<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

use Modules\Compras\Models\Cotizaciones;
use Modules\Compras\Models\CotizacionesProveedores;
use Modules\Compras\Models\Proveedores;
use Modules\Compras\Models\DetallesCotizacion;
use Modules\Compras\Models\OrdenCompra;
use Modules\Compras\Transformers\CotizacionesProveedoresResource;

use Modules\Compras\Models\SolicitudesCompra;

class CotizacionesController extends Controller
{
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

    //  Función para recuperar archivos del servidor 
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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('compras::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('compras::create');
    }

    /**
     * Guarda los importes unitarios de la cotización.
     *  Si tiene archivos los guarda en la base de datos.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        foreach ($data['precios'] as $detalleId => $proveedores) {
            foreach ($proveedores as $proveedorId => $precio) {
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
                }
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Se ha guardado correctamente',
            'data' => []
        ]);
    }


    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        //recupera datos de la cotización
        $cotizacion = Cotizaciones::where('solicitudes_compra_id', $id)
        ->first(['id','consideraciones', 'solicitudes_compra_id']);

        // Recupera los detalles de las cotizaciones-proveedores asociadas a la cotización
        if ($cotizacion) {
            $proveedores = CotizacionesProveedores::where('cotizaciones_id', $cotizacion->id)
            ->get([ 'id','proveedores_id', 'cotizaciones_id', 'ruta', 'seleccionado']);
            $data = [];
            foreach ($proveedores as $proveedor) {
                $proveedorId = $proveedor->id;

                $detalles = DetallesCotizacion::where('cotizaciones_proveedores_proveedores_id', $proveedorId)
                ->get([ 'id','importe_unitario', 'detalle_solicitud_id', 'cotizaciones_proveedores_proveedores_id']);

                $nombreProveedor = Proveedores::where('id', $proveedor->proveedores_id)->get([ 'id','nombre', 'correo' ]);
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
        //Actualiza el estatus de la cotización seleccionada
        //Actualiza el status de la solicitud de compra
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
