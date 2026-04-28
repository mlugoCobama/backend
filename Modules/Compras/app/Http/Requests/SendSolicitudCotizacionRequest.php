<?php

namespace Modules\Compras\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Compras\Models\ProveedorContacto;

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
            'proveedores.*.proveedor_id' => 'required|integer|exists:com_proveedores,id',
            'proveedores.*.contacto_id' => 'nullable|integer|exists:com_proveedor_contactos,id',
            'solicitudes_compra_id' => 'required|integer',
        ];
    }

    /**
     * Validación para evitar que coticen con el mismo proveedor dos veces
     */
    public function withValidator($validator)
{
    $validator->after(function ($validator) {

        $items = $this->input('proveedores', []);

        $proveedorIds = collect($items)
            ->pluck('proveedor_id')
            ->filter()
            ->toArray();

        if (count($proveedorIds) !== count(array_unique($proveedorIds))) {
            $validator->errors()->add(
                'proveedores',
                'No puedes seleccionar el mismo proveedor más de una vez.'
            );
        }

        foreach ($items as $index => $item) {
            if (!empty($item['contacto_id'])) {
                $existe =  ProveedorContacto::where('id', $item['contacto_id'])
                    ->where('proveedor_id', $item['proveedor_id'])
                    ->exists();

                if (!$existe) {
                    $validator->errors()->add(
                        "proveedores.$index.contacto_id",
                        'El contacto no pertenece al proveedor seleccionado.'
                    );
                }
            }
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
