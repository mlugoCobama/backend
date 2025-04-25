<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//Models 
use Modules\Compras\Models\ExpedientesProveedores;
use Modules\Compras\Models\Proveedores;
//Transformers
use Modules\Compras\Transformers\ProveedoresResource;
//Utilities 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class ProveedoresController extends Controller
{
    /** *************************************************************************
     * Recupera todos los datos de proveedores
     ***************************************************************************/
    public function index()
    {
        return ProveedoresResource::collection((Proveedores::active()->get()));
    }

    /** **************************************************************************
     * Recupera unicamente tres datos para llenar los select
     *****************************************************************************/
    public function getProveedores()
    {
        $data = (Proveedores::active()
            ->get([
                'id',
                'nombre',
               
            ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha realizado correctamente',
            'data' => $data
        ]);
    }



    public function create()
    {
        return view('compras::create');
    }

    /** ********************************************************************************
     * Función que valida la información y coordina 
     * storeProveedor y storeExpedienteProveedor
     ***********************************************************************************/
    public function store(Request $request)
    {
     $mensajes = [
        'correo.unique' => 'El correo ya es usado por otro proveedor',
        'telefono.unique' => 'El telefono ya es usado por otro proveedor'
     ];
     
     $validacion =  Validator::make($request->all(), [
         'nombre' => 'required|string',
         'contacto' => 'required|string',
         'telefono' => 'required|string|unique:com_proveedores,telefono',
         'localidad' => 'required|string',
         'condiciones' => 'required|string',
         'servicios' => 'required|string',
         'correo' => 'required|email |unique:com_proveedores,correo',
        'dias_credito' => 'nullable|integer',
         'horario_atencion' => 'required|string',
         'tiempo_entrega' => 'required|string',
         //Validacion para archivos
         'constancia_fiscal' => 'nullable|file|mimes:pdf',
         'ine' => 'nullable|file|mimes:pdf',
         'comprobante_domicilio' => 'nullable|file|mimes:pdf',
         'estado_cuenta' => 'nullable|file|mimes:pdf',
         'acta_constitutiva' => 'nullable|file|mimes:pdf',
         'poder_notarial' => 'nullable|file|mimes:pdf',
     ], $mensajes);

     if ($validacion->fails()) {
         return response()->json([
             'status' => 'error',
             'message' => 'Los datos ingresados no son validos o están incompletos',
             'errors' => $validacion->errors()
         ]);
     }

        try {
            DB::beginTransaction();

                $idProveedor =  $this->storeProveedor($request);

                $this->storeExpedienteProveedor($request, $idProveedor);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha guardado correctamente',
                'data' => []
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Algo salio mal, intente nuevamente',
                'error' => $e->getMessage()
            ]);
        }
    }

    /** ********************************************************************************
     * Función que almacena los datos del proveedor en a base de datos 
     **********************************************************************************/
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

    /** ********************************************************************************
     * Función que almacena los archivos en el servidor y las rutas en la base de datos
     **********************************************************************************/
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

    /** *********************************************************************************
     * Función que recupera las rutas del expediente del proveedor
     ***********************************************************************************/
    public function show($id)
    {
        $expediente = ExpedientesProveedores::where('proveedores_id', $id)->first();
        return response()->json($expediente);
    }

    public function edit($id)
    {
        return view('compras::edit');
    }

    /** **********************************************************************************
     * Fucnion que recibe datos y coordina el funcionamiento de
     * updateProveedor y updateExpedietProveedor
     **************************************************************************************/
    public function update(Request $request, $id)
    {
        $mensajes = [
            'correo.unique' => 'El correo ya es usado por otro proveedor',
            'telefono.unique' => 'El telefono ya es usado por otro proveedor'
         ];
         
         $validacion =  Validator::make($request->all(), [
             'nombre' => 'required|string',
             'contacto' => 'required|string',
             'telefono' => 'required|string|unique:com_proveedores,telefono'.$id,
             'localidad' => 'required|string',
             'condiciones' => 'required|string',
             'servicios' => 'required|string',
             'correo' => 'required|email |unique:com_proveedores,correo'.$id,
            'dias_credito' => 'nullable|integer',
             'horario_atencion' => 'required|string',
             'tiempo_entrega' => 'required|string',
             //Validacion para archivos
             'constancia_fiscal' => 'nullable|file|mimes:pdf',
             'ine' => 'nullable|file|mimes:pdf',
             'comprobante_domicilio' => 'nullable|file|mimes:pdf',
             'estado_cuenta' => 'nullable|file|mimes:pdf',
             'acta_constitutiva' => 'nullable|file|mimes:pdf',
             'poder_notarial' => 'nullable|file|mimes:pdf',
         ], $mensajes);
    
         if ($validacion->fails()) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'Los datos ingresados no son validos o están incompletos',
                 'errors' => $validacion->errors()
             ]);
         }

        try {
            DB::beginTransaction();

                $this->updateProveedor($request, $id);
                $this->updateExpedienteProveedor($request, $id);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha actualizado correctamente',
                'data' => [],
                'id' => $id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar proveedor',
                'error' => $e->getMessage()
            ]);
        }
    }

    /** *********************************************************************
     * Función para actualizar UNICAMENTE los datos del proveedor
     *************************************************************************/
    private function updateProveedor($data, $id)
    {
        $proveedor = Proveedores::find($id);
        if (!$proveedor) {
            throw new \Exception("Proveedor no encontrado");
        }

        $proveedor->update([

            'nombre' => $data->nombre,
            'contacto' => $data->contacto,
            'telefono' => $data->telefono,
            'localidad' => $data->localidad,
            'condiciones' => $data->condiciones,
            'servicios' => $data->servicios,
            'correo' => $data->correo,
            'dias_credito' => $data->dias_credito,
            'horario_atencion ' => $data->horario_atencion,
            'tiempo_entrega' => $data->tiempo_entrega,

        ]);
    }
    /** ***********************************************************************
    *FUNCIÓN QUE ACTUALIZA LOS ARCHIVOS DEL EXPEDIENTE DEL PROVEEDOR
    **************************************************************************/
    private function updateExpedienteProveedor($data, $idProveedor)
    {
        $hoy = date("jnY"); //Recuperar la fecha del dia de hoy para diferenciar el registro nuevo
        $expediente = ExpedientesProveedores::where('proveedores_id', $idProveedor)->first();

        $carpetaProveedor = 'expedientes/' . $idProveedor;

        Storage::makeDirectory($carpetaProveedor);
            if ($data->hasFile('constancia_fiscal')) {

                $archivoEliminar = $expediente->constancia_fiscal; //Recuperarla anterior ruta del archivo al a eliminar
                if ($archivoEliminar) { // verificar si existe la ruta
                    Storage::delete($archivoEliminar); //Borrar el antiguo archivo
                }

                $constancia_fiscal = "constancia_fiscal" . $hoy . "." . $data->file('constancia_fiscal')->getClientOriginalExtension(); //Asignar un nombre al archivo
                $expediente->constancia_fiscal = $data->file('constancia_fiscal')->storeAs($carpetaProveedor, $constancia_fiscal); //Actualiza la ruta y el archivo
            }
            if ($data->hasFile('ine')) {

                $archivoEliminar = $expediente->ine;
                if ($archivoEliminar) { // verificar si existe la ruta
                    Storage::delete($archivoEliminar); //Borrar el antiguo archivo
                }

                $nombreArchivo = "ine" . $hoy . "." . $data->file('ine')->getClientOriginalExtension();
                $expediente->ine = $data->file('ine')->storeAs($carpetaProveedor, $nombreArchivo);
            }
            if ($data->hasFile('comprobante_domicilio')) {

                $archivoEliminar = $expediente->comprobante_domicilio;
                if ($archivoEliminar) { // verificar si existe la ruta
                    Storage::delete($archivoEliminar); //Borrar el antiguo archivo
                }

                $nombreArchivo = "comprobante_domicilio" . $hoy . "." . $data->file('comprobante_domicilio')->getClientOriginalExtension();
                $expediente->comprobante_domicilio = $data->file('comprobante_domicilio')->storeAs($carpetaProveedor, $nombreArchivo);
            }
            if ($data->hasFile('estado_cuenta')) {

                $archivoEliminar = $expediente->estado_cuenta;
                if ($archivoEliminar) { // verificar si existe la ruta
                    Storage::delete($archivoEliminar); //Borrar el antiguo archivo
                }

                $nombreArchivo = "estado_cuenta" . $hoy . "." . $data->file('estado_cuenta')->getClientOriginalExtension();
                $expediente->estado_cuenta = $data->file('estado_cuenta')->storeAs($carpetaProveedor, $nombreArchivo);
            }
            if ($data->hasFile('acta_constitutiva')) {

                $archivoEliminar = $expediente->acta_constitutiva;
                if ($archivoEliminar) { // verificar si existe la ruta
                    Storage::delete($archivoEliminar); //Borrar el antiguo archivo
                }

                $nombreArchivo = "acta_constitutiva" . $hoy . "." . $data->file('acta_constitutiva')->getClientOriginalExtension();
                $expediente->acta_constitutiva = $data->file('acta_constitutiva')->storeAs($carpetaProveedor, $nombreArchivo);
            }
            if ($data->hasFile('poder_notarial')) {

                $archivoEliminar = $expediente->poder_notarial;
                if ($archivoEliminar) { // verificar si existe la ruta
                    Storage::delete($archivoEliminar); //Borrar el antiguo archivo
                }

                $nombreArchivo = "poder_notarial" . $hoy . "." . $data->file('poder_notarial')->getClientOriginalExtension();
                $expediente->poder_notarial = $data->file('poder_notarial')->storeAs($carpetaProveedor, $nombreArchivo);
            }

        $expediente->save();
    }

    /** *******************************************************************************************
     * Actualiza el estatus del proveedor a inactivo
     *********************************************************************************************/
    public function destroy($id)
    {
        $proveedor = Proveedores::where('id', $id);

        if (!$proveedor) {
            return response()->json([
                'status' => 'error',
                'message' => 'El registro que intentas eliminar no existe',
                'data' => []
            ]);
        }

        $proveedor->update([
            'activo' => 0
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha eliminado correctamente',
            'data' => []
        ]);
    }
}
