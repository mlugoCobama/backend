<?php

namespace Modules\Compras\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCatProductoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.id'                => ['nullable'],
            'items.*.categoria'         => ['required', 'string'],
            'items.*.subcategoria_id'   => ['required'],
            'items.*.clave_interna'     => ['required', 'string'],
            'items.*.cant_default'      => ['required', 'integer', 'min:1'],
            'items.*.cat_unidad_medida_id' => ['required'],
            'items.*.descripcion_planta'=> ['nullable', 'string'],
            'items.*.descripcion'       => ['nullable', 'string'],
            'items.*.observaciones'     => ['nullable', 'string'],
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
            'items.required' => 'Debe enviar al menos un item.',
            'items.array'    => 'El formato de los items debe ser un arreglo.',
            'items.min'      => 'Debe incluir al menos :min item.',
            'items.*.categoria.required' => 'La categoría es obligatoria en cada item.',
            'items.*.clave_interna.required' => 'La clave interna es obligatoria en cada item.',
            'items.*.cant_default.min' => 'La cantidad por defecto debe ser al menos :min.',
        ];
    }
}
