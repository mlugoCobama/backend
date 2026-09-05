<?php

namespace Modules\Compras\Http\Controllers;

use App\Exports\ProveedoresExport;
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
use Maatwebsite\Excel\Facades\Excel;
use Modules\Compras\Http\Requests\UpdateProveedoresRequest;
use Modules\Compras\Models\Categorias;
use Modules\Compras\Models\DatosPagoProveedor;
use Modules\Compras\Models\ProveedorContacto;
use Modules\Compras\Models\ProveedorProducto;
use Modules\Compras\Models\ProveedorZona;

class ProveedoresController extends Controller
{
    /**
     * Recupera todos los datos de proveedores
    */
    public function index()
    {
        return ProveedoresResource::collection((Proveedores::with([
            'datosPago',
            'contactos',
            'productos',
            'Expediente',
            'contactos.zona'
             ])->active()->get()));
    }

    /**
     * Crea un nuevo proveedor con toda su información relacionada
     * @param $request Datos validados del proveedor, contactos y archivos
     */
    public function store(ProveedoresRequest $request)
    {

        try {
            DB::beginTransaction();

            $proveedor =  $this->storeProveedor($request['proveedor']);

            //Almacenado de productos
            $productos = $request['proveedor']['productos'] ? $this->productosFormateados($request['proveedor']['productos']) : null;
            if (!empty($productos)) {
                $this->storeProductos($proveedor['id'], $productos, $request['proveedor']['servicios']);
            }

            // Almacenado de contactos
            if (isset($request['contactos']) && !empty($request['contactos']['contactos'])) {
                $this->storeContacto($proveedor['id'], $request['contactos']['contactos']);
            }

            if (isset($request['datosPago']) && !empty($request['datosPago']['datosPago'])) {
                $this->storeDatosPago($proveedor['id'], $request['datosPago']['datosPago']);
            }

            // Almacenado de expediente
            $this->storeExpedienteProveedor($request, $proveedor['id']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha guardado correctamente',
                'data' => $request->all()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Algo salio mal, intente nuevamente',
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
        }
    }


    /**
     * Recupera información del expediente de un proveedor específico
     *
     * @param int $id ID del proveedor
     */
    public function show($id)
    {
        $archivos = ['constancia_fiscal', 'ine', 'comprobante_domicilio', 'estado_cuenta', 'acta_constitutiva', 'poder_notarial', 'contrato', 'opinion_cumplimiento' ];
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

    /**
     * Actualiza la información de un proveedor existente
     *
     * @param $request Datos validados para actualizar
     * @param $id ID del proveedor a actualizar
     */
    public function update(UpdateProveedoresRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $this->updateProveedor($request['proveedor'], $id);

            if (isset($request['change_contactos']) && $request['change_contactos'] == "1") {

                if (isset($request['contactos']) && !empty($request['contactos']['contactos'])) {
                    $this->deleteZonasContactos($id);
                    $this->storeContacto($id, $request['contactos']['contactos']);
                    // $this->storeZona($id, $request['contactos']['contactos']);
                }
            }

            if (isset($request['change_productos']) && $request['change_productos'] == "1") {
                $productos = $request['proveedor']['productos'] ? $this->productosFormateados($request['proveedor']['productos']) : null;
                $this->deleteProductoProveedor($id);
                if (!empty($productos)) {
                    $this->storeProductos($id, $productos, $request['proveedor']['servicios']);
                }
            }

            if (isset($request['change_datosPago']) && $request['change_datosPago'] == "1") {

                if (isset($request['datosPago']) && !empty($request['datosPago']['datosPago'])) {
                    $this->deleteDatosPago($id);
                    $this->storeDatosPago($id, $request['datosPago']['datosPago']);
                    // $this->storeZona($id, $request['contactos']['contactos']);
                }
            }

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

    /**
     * Actualiza el estatus del proveedor a inactivo
     * @param int $id ID del proveedor a desactivar
     */
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

//  ***************************************************************************
//  ***************************************************************************
//  ***************************************************************************
    /**
     * Valida qué archivos del expediente están disponibles
     */
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

    /**
     * Recupera una lista simplificada de proveedores para elementos select/dropdown
     *
     * Retorna únicamente ID y nombre de los proveedores activos,
     * optimizado para su uso en formularios y listas desplegables
     */
    public function getProveedores()
    {
        $data = (Proveedores::active()->with('contactos')
            ->get(['id', 'nombre', 'servicios']));

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha realizado correctamente',
            'data' => $data
        ]);
    }

    /**
     * Almacena un nuevo proveedor en la base de datos
     *
     * Verifica si ya existe un proveedor con el mismo RFC:
     * - Si existe: retorna el ID del proveedor existente
     * - Si no existe: crea un nuevo registro con todos los datos
     *
     * @param $proveedor Datos del proveedor a almacenar
     *                         Incluye: nombre, rfc, contacto, teléfono, localidad, etc.
     * @return ['id' => int, 'isNew' => bool]
     *               - id: ID del proveedor (nuevo o existente)
     *               - isNew: true si se creó nuevo, false si ya existía
     */
    private function storeProveedor($proveedor)
    {
        $exist = Proveedores::where('rfc', $proveedor["rfc"])->first();

        if (!$exist) {
            $dataProveedor = new Proveedores();
            $dataProveedor->nombre = $proveedor["nombre"];
            $dataProveedor->rfc = $proveedor["rfc"];
            $dataProveedor->contacto = $proveedor["contacto"];
            $dataProveedor->telefono = $proveedor["telefono"];
            $dataProveedor->localidad = $proveedor["localidad"];
            $dataProveedor->condiciones = $proveedor["condiciones"];
            $dataProveedor->servicios = $proveedor["servicios"];
            $dataProveedor->correo = $proveedor["correo"];
            $dataProveedor->dias_credito = $proveedor["condiciones"] == "Contado" ? 0 : $proveedor["dias_credito"];
            $dataProveedor->horario_atencion = $proveedor["horario_atencion"];
            $dataProveedor->tiempo_entrega = $proveedor["tiempo_entrega"];
            $dataProveedor->save();
            return ['id' => $dataProveedor->id, 'isNew' => true];
        } else {
            return ['id' => $exist->id, 'isNew' => false];
        }
    }

   /**
     * Almacena los archivos del expediente en el servidor y registra las rutas en BD
     *
     * @param $data Petición con los archivos adjuntos
     * @param $idProveedor ID del proveedor al que pertenece el expediente
     * @return
     */
    private function storeExpedienteProveedor($data, $idProveedor)
    {
        $expedienteSolicitud = new ExpedientesProveedores();
        $carpetaProveedor = 'expedientes/' . $idProveedor;
        Storage::makeDirectory($carpetaProveedor);

        $documentos = ['constancia_fiscal', 'ine', 'comprobante_domicilio', 'estado_cuenta', 'acta_constitutiva', 'poder_notarial', 'contrato', 'opinion_cumplimiento'];

        for ($i = 0; $i < count($documentos); $i++) {
            if ($data->hasFile($documentos[$i])) {
                $constancia_fiscal = $documentos[$i] . "." . $data->file($documentos[$i])->getClientOriginalExtension();
                $expedienteSolicitud->{$documentos[$i]} = $data->file($documentos[$i])->storeAs($carpetaProveedor, $constancia_fiscal);
            }
        }

        $expedienteSolicitud->proveedores_id = $idProveedor;

        $expedienteSolicitud->save();
    }

    /**
     * Actualiza únicamente los datos básicos del proveedor
     *
     * @param mixed $data Datos actualizados del proveedor
     * @param mixed $id ID del proveedor a actualizar
     * @return
     */
    private function updateProveedor($data, $id)
    {
        $proveedor = Proveedores::find($id);
        if (!$proveedor) {
            throw new \Exception("Proveedor no encontrado");
        }

        $proveedor->update([
            'nombre' => $data['nombre'],
            'rfc' => $data['rfc'],
            'contacto' => $data['contacto'],
            'telefono' => $data['telefono'],
            'localidad' => $data['localidad'],
            'condiciones' => $data['condiciones'],
            'servicios' => $data['servicios'],
            'correo' => $data['correo'],
            'dias_credito' => $data['dias_credito'],
            'horario_atencion ' => $data['horario_atencion'],
            'tiempo_entrega' => $data['tiempo_entrega'],

        ]);
    }
    /**
     * Actualiza los archivos del expediente del proveedor
     *
     * @param $data Petición con los archivos a actualizar
     * @param $idProveedor ID del proveedor
     * @return
     */
    private function updateExpedienteProveedor($data, $idProveedor)
    {
        $hoy = date("jnY"); //Recuperar la fecha del dia de hoy para diferenciar el registro nuevo
        $expediente = ExpedientesProveedores::where('proveedores_id', $idProveedor)->first();

        //Valido que el registro del expediente exista
        if ($expediente) {
            $carpetaProveedor = 'expedientes/' . $idProveedor;

            $documentos = ['constancia_fiscal', 'ine', 'comprobante_domicilio', 'estado_cuenta', 'acta_constitutiva', 'poder_notarial', 'contrato', 'opinion_cumplimiento'];

            Storage::makeDirectory($carpetaProveedor);
            for ($i = 0; $i < count($documentos); $i++) {
                if ($data->hasFile($documentos[$i])) {
                    $archivoEliminar = $expediente->{$documentos[$i]}; //Recuperarla anterior ruta del archivo al a eliminar
                    if ($archivoEliminar && !empty($archivoEliminar)) { // verificar si existe la ruta o que no este vacía
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

    /**
     * Almacena la zona
     * @param mixed $id id del contacto
     * @param mixed $contactos datos contactos en array
     */
    public function storeZona($id, $contactos)
    {
            $zonaProveedor = new ProveedorZona();
            $zonaProveedor->contacto_id = $id;
            $zonaProveedor->nombre_zona = $contactos['zona'] ?? 'test';
            $zonaProveedor->estados = $contactos['estados'] ?? null;
            $zonaProveedor->save();
    }

    /**
     * Almacena los contactos
     * @param mixed $idProveedor id del proveedor
     * @param mixed $dataContacto datos contactos en array
     */
    public function storeContacto($idProveedor, $dataContacto)
    {
        if(count($dataContacto) > 0){
            foreach ($dataContacto as $contacto) {
            $contactoProveedor = new ProveedorContacto();
            $contactoProveedor->proveedor_id = $idProveedor;
            $contactoProveedor->nombre = $contacto['nombre'];
            $contactoProveedor->correo = $contacto['correo'];
            $contactoProveedor->telefono = $contacto['telefono'];
            $contactoProveedor->notas = $contacto['notas'];
            $contactoProveedor->save();
            $this->storeZona($contactoProveedor->id, $contacto);
        }
        }


    }

    /**
     * Elimina las zonas y los contactos
     * @param mixed $idProveedor id del proveedor
     */
    public function deleteZonasContactos($idProveedor)
    {
        $contactos =  ProveedorContacto::where ('proveedor_id', $idProveedor)->get();
        // $zonas = ProveedorZona::where('proveedor_id', $idProveedor)->get();
        if ($contactos) {
            foreach ($contactos as $contacto) {
                ProveedorZona::where('contacto_id', $contacto->id)->delete();
                $contacto->delete();
            }
        }
    }

    /**
     * Formatea el contenido de
     * @param $productos productos separados por comas
     * @return $productosFormateados
     */
    private function productosFormateados($productos)
    {
        $productosFormateados = explode(",", $productos);
        $productosFormateados = array_map('trim', $productosFormateados);
        return $productosFormateados;
    }


    /**
     * Almacena la categoria
     * @param string $nombreCategoria nombre de la categoría
     * @return $categoria id
     */
    public function storeCategoria($nombreCategoria)
    {
        $categoria = Categorias::where('nombre', $nombreCategoria)->first();

        if ($categoria) {
            return $categoria->id;
        }
        $dataCategoria =  new Categorias();
        $dataCategoria->nombre = $nombreCategoria;
        $dataCategoria->descripcion = null;
        $dataCategoria->save();
        return $dataCategoria->id;
    }


    /**
     * Almacena productos
     * @param mixed $idProveedor id del proveedor
     * @param mixed $productos array de productos
     * @param mixed $nombreCategoria nombre de la categoría de los productos
     */
    public function storeProductos($idProveedor, $productos, $nombreCategoria)
    {
        $idCategoria = $this->storeCategoria($nombreCategoria);

        for ($i = 0; $i < count($productos); $i++) {
            $dataProducto = new ProveedorProducto();
            $dataProducto->proveedor_id = $idProveedor;
            $dataProducto->categoria_id = $idCategoria;
            $dataProducto->nombre = $productos[$i];
            $dataProducto->descripcion = null;
            $dataProducto->unidad = null;
            $dataProducto->precio_unitario = 0;
            $dataProducto->save();
        }
    }

    /**
     * Elimina las zonas y los contactos
     * @param mixed $idProveedor id del proveedor
     */
    public function deleteProductoProveedor($idProveedor)
    {
        $productos = ProveedorProducto::where('proveedor_id', $idProveedor)->get();

        if ($productos) {
            foreach ($productos as $producto) {
                ProveedorProducto::where('id', $producto->id)->delete();
                $producto->delete();
            }
        }
    }

    public function storeDatosPago($idProveedor, $dataPagos)
    {
        if(count($dataPagos) > 0){
            foreach ($dataPagos as $datoPago) {
            $datosPagoProveedor = new DatosPagoProveedor();
            $datosPagoProveedor->proveedor_id = $idProveedor;
            $datosPagoProveedor->banco = $datoPago['banco'];
            $datosPagoProveedor->no_cuenta = $datoPago['no_cuenta'];
            $datosPagoProveedor->clave_interbancaria = $datoPago['clave_interbancaria'];
            $datosPagoProveedor->beneficiario = $datoPago['beneficiario'];
            $datosPagoProveedor->save();
        }
        }
    }

    public function deleteDatosPago($idProveedor)
    {
        $datosPago = DatosPagoProveedor::where('proveedor_id', $idProveedor)->get();

        if ($datosPago) {
            foreach ($datosPago as $datoPago) {
                DatosPagoProveedor::where('id', $datoPago->id)->delete();
                $datoPago->delete();
            }
        }
    }

    public function exportExcel()
    {
        return Excel::download(
            new ProveedoresExport(),
            'proveedores.xlsx'
        );
    }
}
