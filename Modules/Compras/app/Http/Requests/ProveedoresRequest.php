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
            // Validar proveedor
            'proveedor.nombre' => 'required|string|max:255',
            'proveedor.rfc' => 'required|string|max:13|unique:com_proveedores,rfc',
            'proveedor.contacto' => 'nullable|string|max:255',
            'proveedor.telefono' => 'nullable|string|unique:com_proveedores,telefono',
            'proveedor.correo' => 'nullable|string|unique:com_proveedores,correo',
            'proveedor.condiciones' => 'nullable|string',
            'proveedor.horario_atencion' => 'nullable|string',
            'proveedor.localidad' => 'nullable|string',
            'proveedor.productos' => 'nullable|string',
            // Validar contactos (si vienen)
            'contactos.contactos' => 'array',
            'contactos.contactos.*.nombre' => 'required_with:contactos.contactos|string',
            'contactos.contactos.*.correo' => 'nullable|string',
            'contactos.contactos.*.telefono' => 'nullable|string|max:20',
            'contactos.contactos.*.zona' => 'nullable|string|max:20',
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

    public function messages(): array
{
    return [
        'proveedor.rfc.unique' => 'El RFC ya está registrado para otro proveedor.',
        'proveedor.correo.unique' => 'El correo ya es usado por otro proveedor.',
        'proveedor.telefono.unique' => 'El teléfono ya es usado por otro proveedor.',
    ];
}

public function prepareForValidation()
{
    // Decodificar los JSON enviados como string
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
