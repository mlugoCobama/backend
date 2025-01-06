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
            'nombre' => 'required',
            'contacto' => 'required',
            'telefono' => 'required',
            'estado' => 'required',
            'condiciones' => 'required',
            'servicios' => 'required',
        ];
    }

    public function messages(): array
{
    return [
        'nombre.required' => 'A title is required',
        'contacto.required' => 'A message is required',
        'telefono.required' => 'A message is required',
        'estado.required' => 'A message is required',
        'condiciones.required' => 'A message is required',
        'servicios.required' => 'A message is required',
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
