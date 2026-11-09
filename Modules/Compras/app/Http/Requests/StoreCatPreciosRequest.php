<?php

namespace Modules\Compras\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCatPreciosRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'productosArray'                              => ['required', 'array'],
            'productosArray.*.producto_id'                => ['required', 'exists:com_productos,id'],
            'productosArray.*.preciosProveedores'         => ['required', 'array'],
            'productosArray.*.preciosProveedores.*.proveedor_id' => ['required', 'exists:com_proveedores,id'],
            'productosArray.*.preciosProveedores.*.precio' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    
    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'productosArray.required' => 'El listado de productos es obligatorio.',
            'productosArray.array'    => 'El formato del listado de productos debe ser un arreglo.',
            
            'productosArray.*.producto_id.required' => 'El producto es obligatorio.',
            'productosArray.*.producto_id.exists'   => 'El producto seleccionado no existe en la base de datos.',
            
            'productosArray.*.preciosProveedores.required' => 'Debe especificar los precios de los proveedores para cada producto.',
            'productosArray.*.preciosProveedores.array'    => 'Los precios de los proveedores deben tener un formato de arreglo.',
            
            'productosArray.*.preciosProveedores.*.proveedor_id.required' => 'El proveedor es obligatorio.',
            'productosArray.*.preciosProveedores.*.proveedor_id.exists'   => 'El proveedor seleccionado no existe en la base de datos.',
            
            'productosArray.*.preciosProveedores.*.precio.required' => 'El precio es obligatorio.',
            'productosArray.*.preciosProveedores.*.precio.numeric'  => 'El precio debe ser un valor numérico.',
            'productosArray.*.preciosProveedores.*.precio.min'      => 'El precio no puede ser menor a 0.',
        ];
    }
}
