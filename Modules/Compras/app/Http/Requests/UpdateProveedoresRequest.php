<?php

namespace Modules\Compras\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class   UpdateProveedoresRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        $proveedorId = $this->route('id');

        return [
            // Validar proveedor
            'proveedor.nombre' => 'required|string|max:255',
            'proveedor.rfc' => [
                'required',
                'string',
                'max:13',
                Rule::unique('com_proveedores', 'rfc')->ignore($proveedorId),
            ],
            'proveedor.contacto' => 'nullable|string|max:255',
            'proveedor.telefono' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('com_proveedores', 'telefono')->ignore($proveedorId),
            ],
            'proveedor.correo' => [
                'nullable',
                'email',
                Rule::unique('com_proveedores', 'correo')->ignore($proveedorId),
            ],
            'proveedor.condiciones' => 'nullable|string',
            'proveedor.horario_atencion' => 'nullable|string',
            'proveedor.localidad' => 'nullable|string',
            'proveedor.productos' => 'nullable|string',

            // Validar contactos
            'contactos.contactos' => 'array',
            'contactos.contactos.*.nombre' => 'required_with:contactos.contactos|string',
            'contactos.contactos.*.correo' => 'nullable|string',
            'contactos.contactos.*.telefono' => 'nullable|string',
            'contactos.contactos.*.zona' => 'nullable|string',
            'contactos.contactos.*.notas' => 'nullable|string',

            // Bandera de cambio
            'change_contactos' => 'nullable',

            'change_productos' => 'nullable',

            // Archivos
            'constancia_fiscal' => 'nullable|file|mimes:pdf',
            'ine' => 'nullable|file|mimes:pdf',
            'comprobante_domicilio' => 'nullable|file|mimes:pdf',
            'estado_cuenta' => 'nullable|file|mimes:pdf',
            'acta_constitutiva' => 'nullable|file|mimes:pdf',
            'poder_notarial' => 'nullable|file|mimes:pdf',
            'contrato' => 'nullable|file|mimes:pdf',
            'opinion_cumplimiento' => 'nullable|file|mimes:pdf',
        ];
    }

    public function messages()
    {
        return [
            'proveedor.correo.unique' => 'El correo ya está registrado por otro proveedor.',
            'proveedor.telefono.unique' => 'El teléfono ya está registrado por otro proveedor.',
            'proveedor.rfc.unique' => 'El RFC ya está registrado por otro proveedor.',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'proveedor' => json_decode($this->proveedor, true),
            'contactos' => json_decode($this->contactos, true),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
