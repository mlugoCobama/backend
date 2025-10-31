<?php

namespace Modules\Compras\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendSolicitudCotizacionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'consideraciones' => 'nullable|string',
            'proveedores' => 'required|array|min:1',
            'proveedores.*' => 'required|integer|exists:com_proveedores,id',
            'solicitudes_compra_id' => 'required|integer|exists:com_solicitudes_compra,id',
        ];
    }

    /**
     * Validación para evitar que coticen con el mismo proveedor dos veces
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que no haya proveedores duplicados
            $proveedores = $this->input('proveedores', []);
            if (count($proveedores) !== count(array_unique($proveedores))) {
                $validator->errors()->add(
                    'proveedores',
                    'No puedes seleccionar el mismo proveedor más de una vez.'
                );
            }
        });
    }

    public function messages()
    {
        return [
            'proveedores.required' => 'Debes seleccionar al menos un proveedor',
            'proveedores.array' => 'El formato de proveedores es inválido',
            'proveedores.min' => 'Debes seleccionar al menos un proveedor',
            'proveedores.*.required' => 'Cada proveedor es obligatorio',
            'proveedores.*.integer' => 'El ID del proveedor debe ser un número entero',
            'proveedores.*.exists' => 'Uno o más proveedores seleccionados no existen',
            'solicitudes_compra_id.required' => 'La solicitud de compra es obligatoria',
            'solicitudes_compra_id.integer' => 'El ID de la solicitud debe ser un número entero',
            'solicitudes_compra_id.exists' => 'La solicitud de compra no existe',
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
