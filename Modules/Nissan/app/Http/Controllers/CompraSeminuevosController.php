<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use Modules\Nissan\Models\NisDatosProveedor;
use Modules\Nissan\Models\NisDatosBancarios;
use Modules\Nissan\Models\NisDatosCFDI;
use Modules\Nissan\Models\NisDatosVehiculos;

class CompraSeminuevosController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('nissan::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('nissan::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $idProveedor = $this->storeDataProveedor($request[0]);
        $this->storeDataCFDI($request[1], $idProveedor);
        $this->storeDataVehiculo($request[2], $idProveedor);
        $this->storeDataBancaria($request[3], $idProveedor);

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
        $data = NisDatosProveedor::where('id', $id)
                                    ->with('dataCfdi')
                                    ->with('dataVehiculo')
                                    ->with('dataBancario')
                                    ->first();

        $pdf = new CompraSeminuevosPDFController();

        //$file = $pdf->checklistPDF($data);
        $file = $pdf->solicitudDFDI($data);

        return $file;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('nissan::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    private function storeDataProveedor($data){
        $dataProveedor = new NisDatosProveedor();
            $dataProveedor->folio = 'A-12';
            $dataProveedor->nombre = $data["nombre"];
            $dataProveedor->apellido_paterno = $data["apellido_paterno"];
            $dataProveedor->apellido_materno = $data["apellido_materno"];
            $dataProveedor->calle = $data["calle"];
            $dataProveedor->num_interior = $data["num_interior"];
            $dataProveedor->num_exterior = $data["num_exterior"];
            $dataProveedor->cp = $data["cp"];
            $dataProveedor->colonia = $data["colonia"];
            $dataProveedor->ciudad = $data["ciudad"];
            $dataProveedor->delegacion_municipio = $data["delegacion_municipio"];
            $dataProveedor->estado = $data["estado"];
            $dataProveedor->tipo_identificacion = $data["tipo_identificacion"];
            $dataProveedor->num_identificacion = $data["num_identificacion"];
            $dataProveedor->mes_comprobante_domicilio = $data["mes_comprobante"];
        $dataProveedor->save();
        return $dataProveedor->id;
    }

    private function storeDataCFDI($data, $idProveedor) {
        $dataCDFI = new NisDatosCFDI();
            $dataCDFI->regimen_fiscal = $data["regimen_fiscal"];
            $dataCDFI->rfc = $data["rfc"];
            $dataCDFI->nombre = $data["nombre"];
            $dataCDFI->apellido_paterno = $data["apellido_paterno"];
            $dataCDFI->apellido_materno = $data["apellido_materno"];
            $dataCDFI->correo_electronico = $data["correo"];
            $dataCDFI->telefono_fijo = $data["telefono_fijo"];
            $dataCDFI->telefono_movil = $data["telefono_movil"];
            $dataCDFI->fecha_nacimiento = $data["fecha_nacimiento"];
            $dataCDFI->entidad_nacimiento = $data["entidad_nacimiento"];
            $dataCDFI->curp = $data["curp"];
            $dataCDFI->pais = $data["pais"];
            $dataCDFI->actividad_preponderante = $data["actividad_prepoderante"];
            $dataCDFI->nis_datos_proveedor_id = $idProveedor;
        $dataCDFI->save();
    }

    private function storeDataVehiculo($data, $idProveedor) {
        $dataVehiculo = new NisDatosVehiculos();
            $dataVehiculo->factura_ampara = $data["factura_ampara"];
            $dataVehiculo->expedida_por = $data["expedida"];
            $dataVehiculo->fecha_factura = $data["fecha_factura"];
            $dataVehiculo->marca = $data["marca"];
            $dataVehiculo->anio_modelo = $data["anio_modelo"];
            $dataVehiculo->color_ext = $data["color_exterior"];
            $dataVehiculo->color_int = $data["color_interior"];
            $dataVehiculo->tipo_version = $data["tipo_version"];
            $dataVehiculo->clase_vehicular = $data["clase_vehicular"];
            $dataVehiculo->num_serie = $data["no_serie"];
            $dataVehiculo->num_motor = $data["no_motor"];
            $dataVehiculo->kilometraje = $data["kilometraje"];
            $dataVehiculo->num_baja_vehicular = $data["no_baja_vehicular"];
            $dataVehiculo->edo_emite_baja = $data["estado_baja"];
            $dataVehiculo->fecha_baja = $data["fecha_baja"];
            $dataVehiculo->anio_tenencia = $data["anio_tenencia"];
            $dataVehiculo->placas_baja = $data["placas_baja"];
            $dataVehiculo->num_verificacion = $data["no_verificacion"];
            $dataVehiculo->valor_compra = $data["valor_compra"];
            $dataVehiculo->valor_guia_autometria = $data["valor_guia"];
            $dataVehiculo->nombre_a_quien_vende = $data["nombre_a_quien_vende"];
            $dataVehiculo->fecha_factura_final = $data["fecha_factura_final"];
            $dataVehiculo->nis_datos_proveedor_id = $idProveedor;
        $dataVehiculo->save();
    }

    private function storeDataBancaria($data, $idProveedor) {
        $dataBancaria = new NisDatosBancarios();
            $dataBancaria->costo_adquisicion = $data["costo_adquisicion"];
            $dataBancaria->banco = $data["banco"];
            $dataBancaria->cuenta = $data["cuenta"];
            $dataBancaria->clabe = $data["clabe"];
            $dataBancaria->sucursal = $data["sucursal"];
            $dataBancaria->convenio = $data["convenio"];
            $dataBancaria->referencia = $data["referencia"];
            $dataBancaria->nis_datos_proveedor_id = $idProveedor;
        $dataBancaria->save();
    }

}
