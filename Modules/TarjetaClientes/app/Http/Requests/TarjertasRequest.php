<?php

namespace Modules\TarjetaClientes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TarjertasRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            // Agencia
            'agencia.agencia' => 'required|string|max:255',
            'agencia.asesor_ventas' => 'required|string|max:255',
            'agencia.no_sicop' => 'nullable|string|max:50',

            // Datos personales
            'personal.nombre_cliente' => 'required|string|max:255',
            'personal.direccion' => 'required|string|max:255',
            'personal.ciudad' => 'required|string|max:100',
            'personal.estado' => 'required|string|max:100',

            // Contacto
            'contacto.email_personal' => 'required|email|max:255',
            'contacto.email_trabajo' => 'nullable|email|max:255',
            'contacto.telefono_principal' => 'required|string|max:20',
            'contacto.telefono_secundario' => 'nullable|string|max:20',
            'contacto.telefono_adicional' => 'nullable|string|max:20',

            // Tipo de cliente
            'tipoCliente.tiene_cita' => 'nullable',
            'tipoCliente.tipo_contacto' => 'nullable|string|max:100',
            'tipoCliente.cual_publicidad' => 'nullable|string|max:255',

            // Atención
            'atencion.servicio' => 'nullable|string|max:255',
            'atencion.notas_apv' => 'nullable|string',
            'atencion.notas_gv' => 'nullable|string',

            // Intención de compra
            'intencionCompra.cliente_quiere' => 'nullable|string|max:255',
            'intencionCompra.anio' => 'nullable|integer|min:1900|max:2100',
            'intencionCompra.modelo' => 'nullable|string|max:255',
            'intencionCompra.estilo' => 'nullable|string|max:255',
            'intencionCompra.color' => 'nullable|string|max:100',
            'intencionCompra.stock_vin' => 'nullable|string|max:100',
            'intencionCompra.equipo_particular' => 'nullable|string|max:255',

            // Vehículo del cliente
            'vehiculoCliente.anio_vehiculo' => 'nullable|integer|min:1900|max:2100',
            'vehiculoCliente.modelo_vehiculo' => 'nullable|string|max:255',
            'vehiculoCliente.estilo_vehiculo' => 'nullable|string|max:255',
            'vehiculoCliente.color_vehiculo' => 'nullable|string|max:100',

            // Opciones del vehículo (booleanos)
            'vehiculoCliente.ac' => 'nullable',
            'vehiculoCliente.pw' => 'nullable',
            'vehiculoCliente.pl' => 'nullable',
            'vehiculoCliente.cruise' => 'nullable',
            'vehiculoCliente.tilt' => 'nullable',
            'vehiculoCliente.auto' => 'nullable',
            'vehiculoCliente.x4x4' => 'nullable',
            'vehiculoCliente.cd' => 'nullable',
            'vehiculoCliente.sat' => 'nullable',
            'vehiculoCliente.navi' => 'nullable',

            // Más datos del vehículo
            'vehiculoCliente.kilometraje' => 'nullable|string',
            'vehiculoCliente.vin' => 'nullable|string|max:50',
            'vehiculoCliente.costo_pagar' => 'nullable|string',
            'vehiculoCliente.acv' => 'nullable|string',
            'vehiculoCliente.telefono_banco' => 'nullable|string|max:20',
        ];
    }


    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
