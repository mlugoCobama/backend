<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//Models 
use Modules\Compras\Models\ExpedientesProveedores;
use Modules\Compras\Models\Proveedores;
//Transformers
use Modules\Compras\Transformers\ProveedoresResource;
use Modules\Compras\Http\Requests\ProveedoresRequest;
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

    /** ********************************************************************************
     * Función que valida la información y coordina, 
     * storeProveedor y storeExpedienteProveedor
     ***********************************************************************************/
    public function store(ProveedoresRequest $request)
    {

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


    /** *********************************************************************************
     * Función que recupera las rutas del expediente del proveedor
     ***********************************************************************************/
    public function show($id)
    {
        $archivos = ['constancia_fiscal', 'ine', 'comprobante_domicilio', 'estado_cuenta', 'acta_constitutiva', 'poder_notarial'];
        $expediente = ExpedientesProveedores::where('proveedores_id', $id)->first();

        $archivosDisponibles = $this->validarExpediente($expediente, $archivos);

        $habilitarDescarga =  false;
        $tamanio = count($archivosDisponibles);
        if ($tamanio > 0) {
            $habilitarDescarga =  true;
        }

        return response()->json([
            'data' => $expediente,
            'descargable' => $habilitarDescarga,
            'tamanio' => $tamanio
        ]);
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
            'telefono' => 'required|string|unique:com_proveedores,telefono,' . $id,
            'localidad' => 'required|string',
            'condiciones' => 'required|string',
            'servicios' => 'required|string',
            'correo' => 'required|email |unique:com_proveedores,correo,' . $id,
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
    
    public function validarExpediente($rutas, $archivos)
    {
        $archivosDisponibles = [];
        foreach ($archivos as $archivo) {
            if (!empty($rutas[$archivo])) {
                $archivosDisponibles[] = $archivo;
            }
        }
        return $archivosDisponibles;
    }
    
    /** **************************************************************************
     * Recupera unicamente tres datos para llenar los select
     *****************************************************************************/
    public function getProveedores()
    {
        $data = (Proveedores::active()
            ->get(['id', 'nombre',]));

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha realizado correctamente',
            'data' => $data
        ]);
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

        $documentos = ['constancia_fiscal', 'ine', 'comprobante_domicilio', 'estado_cuenta', 'acta_constitutiva', 'poder_notarial'];

        for ($i = 0; $i < count($documentos); $i++) {
            if ($data->hasFile($documentos[$i])) {
                $constancia_fiscal = $documentos[$i] . "." . $data->file($documentos[$i])->getClientOriginalExtension();
                $expedienteSolicitud->{$documentos[$i]} = $data->file($documentos[$i])->storeAs($carpetaProveedor, $constancia_fiscal);
            }
        }

        $expedienteSolicitud->proveedores_id = $idProveedor;

        $expedienteSolicitud->save();
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

        //Valido que el registro del expediente exista
        if ($expediente) {
            $carpetaProveedor = 'expedientes/' . $idProveedor;

            $documentos = ['constancia_fiscal', 'ine', 'comprobante_domicilio', 'estado_cuenta', 'acta_constitutiva', 'poder_notarial'];

            Storage::makeDirectory($carpetaProveedor);
            for ($i = 0; $i < count($documentos); $i++) {
                if ($data->hasFile($documentos[$i])) {
                    $archivoEliminar = $expediente->{$documentos[$i]}; //Recuperarla anterior ruta del archivo al a eliminar
                    if ($archivoEliminar) { // verificar si existe la ruta
                        Storage::delete($archivoEliminar); //Borrar el antiguo archivo
                    }
                    $constancia_fiscal = $documentos[$i] . $hoy . "." . $data->file($documentos[$i])->getClientOriginalExtension(); //Asignar un nombre al archivo
                    $expediente->{$documentos[$i]} = $data->file($documentos[$i])->storeAs($carpetaProveedor, $constancia_fiscal); //Actualiza la ruta y el archivo
                }
            }

            $expediente->save();
        } else //Si no existe crea el registro
        {
            $this->storeExpedienteProveedor($data, $idProveedor);
        }
    }
}
