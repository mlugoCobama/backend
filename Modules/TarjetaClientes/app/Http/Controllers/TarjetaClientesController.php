<?php

namespace Modules\TarjetaClientes\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\TarjetaClientes\Http\Requests\TarjertasRequest;
use Modules\TarjetaClientes\Models\TarjetaCliente;
use Modules\TarjetaClientes\Transformers\TarjetaClienteResource;

class TarjetaClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data =  TarjetaCliente::get();

        return response()->json([
            'data' => TarjetaClienteResource::collection($data),
            'status' => 'success',
            'message' => 'Datos recuperados correctamente'
        ]);

        // return view('tarjetaclientes::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tarjetaclientes::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TarjertasRequest $request)
{
    $tarjeta = new TarjetaCliente();

    $tarjeta->folio = $this->generarFolioTC();
    $tarjeta->fecha = date('Y-m-d H:i:s') ?? now();
     
        $tarjeta->agencia = $request->input('agencia.agencia');
        $tarjeta->asesor_ventas = $request->input('agencia.asesor_ventas');
        $tarjeta->no_sicop = $request->input('agencia.no_sicop');

        $tarjeta->nombre_cliente = $request->input('personal.nombre_cliente');
        $tarjeta->direccion = $request->input('personal.direccion');
        $tarjeta->ciudad = $request->input('personal.ciudad');
        $tarjeta->estado = $request->input('personal.estado');

        $tarjeta->email_personal = $request->input('contacto.email_personal');
        $tarjeta->email_trabajo = $request->input('contacto.email_trabajo');
        $tarjeta->telefono_principal = $request->input('contacto.telefono_principal');
        $tarjeta->telefono_secundario = $request->input('contacto.telefono_secundario');
        $tarjeta->telefono_adicional = $request->input('contacto.telefono_adicional');

        $tarjeta->tiene_cita = $request->boolean('tipoCliente.tiene_cita');
        $tarjeta->tipo_contacto = $request->input('tipoCliente.tipo_contacto');
        $tarjeta->cual_publicidad = $request->input('tipoCliente.cual_publicidad');

        $tarjeta->servicio = $request->input('atencion.servicio');
        $tarjeta->notas_apv = $request->input('atencion.notas_apv');
        $tarjeta->notas_gv = $request->input('atencion.notas_gv');

        $tarjeta->cliente_quiere = $request->input('intencionCompra.cliente_quiere');
        $tarjeta->anio = $request->input('intencionCompra.anio');
        $tarjeta->modelo = $request->input('intencionCompra.modelo');
        $tarjeta->estilo = $request->input('intencionCompra.estilo');
        $tarjeta->color = $request->input('intencionCompra.color');
        $tarjeta->stock_vin = $request->input('intencionCompra.stock_vin');
        $tarjeta->equipo_particular = $request->input('intencionCompra.equipo_particular');

        $tarjeta->anio_vehiculo = $request->input('vehiculoCliente.anio_vehiculo');
        $tarjeta->modelo_vehiculo = $request->input('vehiculoCliente.modelo_vehiculo');
        $tarjeta->estilo_vehiculo = $request->input('vehiculoCliente.estilo_vehiculo');
        $tarjeta->color_vehiculo = $request->input('vehiculoCliente.color_vehiculo');

        // Booleanos
        $tarjeta->ac = $request->boolean('vehiculoCliente.ac');
        $tarjeta->pw = $request->boolean('vehiculoCliente.pw');
        $tarjeta->pl = $request->boolean('vehiculoCliente.pl');
        $tarjeta->cruise = $request->boolean('vehiculoCliente.cruise');
        $tarjeta->tilt = $request->boolean('vehiculoCliente.tilt');
        $tarjeta->auto = $request->boolean('vehiculoCliente.auto');
        $tarjeta->x4x4 = $request->boolean('vehiculoCliente.x4x4');
        $tarjeta->cd = $request->boolean('vehiculoCliente.cd');
        $tarjeta->sat = $request->boolean('vehiculoCliente.sat');
        $tarjeta->navi = $request->boolean('vehiculoCliente.navi');

        $tarjeta->kilometraje = $request->input('vehiculoCliente.kilometraje');
        $tarjeta->vin = $request->input('vehiculoCliente.vin');
        $tarjeta->costo_pagar = $request->input('vehiculoCliente.costo_pagar');
        $tarjeta->acv = $request->input('vehiculoCliente.acv');
        $tarjeta->telefono_banco = $request->input('vehiculoCliente.telefono_banco');



    $tarjeta->save();

    return response()->json([
        'message' => 'Tarjeta del cliente guardada correctamente.',
        'data' => $tarjeta
    ], 201);
}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('tarjetaclientes::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('tarjetaclientes::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
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

    public function generarFolioTC()
    {
        $ultimaTC = TarjetaCliente::orderBy('id', 'desc')->first('folio');
        if ($ultimaTC) {
            $ultimoFolio = $ultimaTC->folio;
            $numero = intval(substr($ultimoFolio, 3)) + 1;
        } else {
            $numero = 1;
        }
        $nuevoFolio = 'TC-' . str_pad($numero, 5, '0', STR_PAD_LEFT);

        return $nuevoFolio;
    }
}
