<?php

namespace Modules\Compras\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProveedoresRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
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
        ];
    }

    public function messages(): array
{
    return [
        'correo.unique' => 'El correo ya es usado por otro proveedor',
        'telefono.unique' => 'El telefono ya es usado por otro proveedor'
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
