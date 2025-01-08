<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

use Modules\Compras\Models\ExpedientesProveedores;

use Modules\Compras\Models\Proveedores;

/**
 * Resources
 */

use Modules\Compras\Transformers\ProveedoresResource;

class ProveedoresController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProveedoresResource::collection((Proveedores::active()->get()));
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
        //  $proveedor = proveedores::create($request->all());
        //  $expediente = ExpedientesProveedores::create($request->all(), $proveedor);

        $idProveedor =  $this->storeProveedor($request);

        $this->storeExpedienteProveedor($request, $idProveedor);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha guardado correctamente',
            //'data' => new ProveedoresResource($proveedor),
            'data' => []
        ]);
    }


    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $expediente = ExpedientesProveedores::where('proveedores_id', $id)->first();
        return response()->json($expediente);
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

        $this->updateProveedor($request, $id);

        $this->updateExpedienteProveedor($request, $id);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha actualizado correctamente',
            'data' => '',
            'id' => $id,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Proveedores::where('id', $id)->update(['activo' => 0]);
        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => ''
        ]);
    }



    private function storeProveedor($data)
    {
        $dataProveedor = new Proveedores();
        $dataProveedor->nombre = $data["nombre"];
        $dataProveedor->contacto = $data["contacto"];
        $dataProveedor->telefono = $data["telefono"];
        $dataProveedor->localidad = $data["localidad"];
        $dataProveedor->condiciones = $data["condiciones"];
        $dataProveedor->servicios = $data["servicios"];
        $dataProveedor->correo = $data["correo"];
        $dataProveedor->dias_credito = $data["dias_credito"];
        $dataProveedor->horario_atencion = $data["horario_atencion"];
        $dataProveedor->tiempo_entrega = $data["tiempo_entrega"];
        $dataProveedor->save();
        return $dataProveedor->id;
    }

    private function storeExpedienteProveedor($data, $idProveedor)
    {
        $expedienteSolicitud = new ExpedientesProveedores();
        $carpetaProveedor = 'expedientes/' . $idProveedor;
        Storage::makeDirectory($carpetaProveedor);

        if ($data->hasFile('constancia_fiscal')) {
            $constancia_fiscal = "constancia_fiscal" . "." . $data->file('constancia_fiscal')->getClientOriginalExtension();
            $expedienteSolicitud->constancia_fiscal = $data->file('constancia_fiscal')->storeAs($carpetaProveedor, $constancia_fiscal);
        }
        if ($data->hasFile('ine')) {
            $nombreArchivo = "ine" . "." . $data->file('ine')->getClientOriginalExtension();
            $expedienteSolicitud->ine = $data->file('ine')->storeAs($carpetaProveedor, $nombreArchivo);
        }
        if ($data->hasFile('comprobante_domicilio')) {
            $nombreArchivo = "comprobante_domicilio" . "." . $data->file('comprobante_domicilio')->getClientOriginalExtension();
            $expedienteSolicitud->comprobante_domicilio = $data->file('comprobante_domicilio')->storeAs($carpetaProveedor, $nombreArchivo);
        }
        if ($data->hasFile('estado_cuenta')) {
            $nombreArchivo = "estado_cuenta" . "." . $data->file('estado_cuenta')->getClientOriginalExtension();
            $expedienteSolicitud->estado_cuenta = $data->file('estado_cuenta')->storeAs($carpetaProveedor, $nombreArchivo);
        }
        if ($data->hasFile('acta_constitutiva')) {
            $nombreArchivo = "acta_constitutiva" . "." . $data->file('acta_constitutiva')->getClientOriginalExtension();
            $expedienteSolicitud->acta_constitutiva = $data->file('acta_constitutiva')->storeAs($carpetaProveedor, $nombreArchivo);
        }

        if ($data->hasFile('poder_notarial')) {
            $nombreArchivo = "poder_notarial" . "." . $data->file('poder_notarial')->getClientOriginalExtension();
            $expedienteSolicitud->poder_notarial = $data->file('poder_notarial')->storeAs($carpetaProveedor, $nombreArchivo);
        }

        $expedienteSolicitud->proveedores_id = $idProveedor;
        $expedienteSolicitud->save();
    }

    private function updateProveedor($data, $id)
    {
        $proveedor = Proveedores::find($id);
        $proveedor->nombre = $data->nombre;
        $proveedor->contacto = $data->contacto;
        $proveedor->telefono = $data->telefono;
        $proveedor->localidad = $data->localidad;
        $proveedor->condiciones = $data->condiciones;
        $proveedor->servicios = $data->servicios;
        $proveedor->correo = $data->correo;
        $proveedor->dias_credito = $data->dias_credito;
        $proveedor->horario_atencion = $data->horario_atencion;
        $proveedor->tiempo_entrega = $data->tiempo_entrega;

        $proveedor->save();
    }
    /*---------------------------------------------------------------------
*
    *Primero busco al proveedor
    *Elimino el anterior archivo
    *Almaceno los archivos 
    *Despues almaceno las rutas del expediente
    *Finalemnte mostramos la solicitud con los detalles
*---------------------------------------------------------------------
*/
    private function updateExpedienteProveedor($data, $idProveedor)
    {
        $hoy = date("jnY"); //Recuperar la fecha del dia de hoy para diferenciar el registro nuevo
        $expediente = ExpedientesProveedores::where('proveedores_id', $idProveedor)->first();

        $carpetaProveedor = 'expedientes/' . $idProveedor;

        Storage::makeDirectory($carpetaProveedor);
        if ($data->hasFile('constancia_fiscal')) {

            $archivoEliminar = $expediente->constancia_fiscal; //Recuperarla anterior ruta del archivo al a eliminar
            Storage::delete($archivoEliminar); //Borrar el antiguo archivo

            $constancia_fiscal = "constancia_fiscal" . $hoy . "." . $data->file('constancia_fiscal')->getClientOriginalExtension(); //Asignar un nombre al archivo
            //$constancia_fiscal = "constancia_fiscal.". $data->file('constancia_fiscal')->getClientOriginalExtension();
            $expediente->constancia_fiscal = $data->file('constancia_fiscal')->storeAs($carpetaProveedor, $constancia_fiscal); //Actualiza la ruta y el archivo
        }
        if ($data->hasFile('ine')) {

            $archivoEliminar = $expediente->ine;
            Storage::delete($archivoEliminar);

                $nombreArchivo = "ine". $hoy . "." . $data->file('ine')->getClientOriginalExtension();
                $expediente->ine = $data->file('ine')->storeAs($carpetaProveedor, $nombreArchivo);
        }
        if ($data->hasFile('comprobante_domicilio')) {

            $archivoEliminar = $expediente->comprobante_domicilio;
            Storage::delete($archivoEliminar);

                $nombreArchivo = "comprobante_domicilio" . $hoy . ".". $data->file('comprobante_domicilio')->getClientOriginalExtension();
                $expediente->comprobante_domicilio = $data->file('comprobante_domicilio')->storeAs($carpetaProveedor, $nombreArchivo);
        }
        if ($data->hasFile('estado_cuenta')) {

            $archivoEliminar = $expediente->estado_cuenta;
            Storage::delete($archivoEliminar);

                $nombreArchivo = "estado_cuenta" . $hoy . ".". $data->file('estado_cuenta')->getClientOriginalExtension();
                $expediente->estado_cuenta = $data->file('estado_cuenta')->storeAs($carpetaProveedor, $nombreArchivo);
        }
        if ($data->hasFile('acta_constitutiva')) {

            $archivoEliminar = $expediente->acta_constitutiva;
            Storage::delete($archivoEliminar);

                $nombreArchivo = "acta_constitutiva". $hoy . "." . $data->file('acta_constitutiva')->getClientOriginalExtension();
                $expediente->acta_constitutiva = $data->file('acta_constitutiva')->storeAs($carpetaProveedor, $nombreArchivo);
        }
        if ($data->hasFile('poder_notarial')) {

            $archivoEliminar = $expediente->poder_notarial;
            Storage::delete($archivoEliminar);
            
                $nombreArchivo = "poder_notarial" . $hoy . ".". $data->file('poder_notarial')->getClientOriginalExtension();
                $expediente->poder_notarial = $data->file('poder_notarial')->storeAs($carpetaProveedor, $nombreArchivo);
        }

        $expediente->save();

    }
}
