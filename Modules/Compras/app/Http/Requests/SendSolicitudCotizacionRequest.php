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
            'proveedor1' => 'required|integer',
            'proveedor2' => 'nullable|integer',
            'proveedor3' => 'nullable|integer',
            'solicitudes_compra_id' => 'required|integer',
        ];
    }

    public function messages()
    {
        return
        [
            'proveedor1.required' => 'El proveedor 1 es obligatorio para continuar',
            'proveedor2.required' => 'El proveedor 2 es obligatorio para continuar',
            'proveedor3.required' => 'El proveedor 3 es obligatorio para continuar',
            'proveedor3.required' => 'Selecciona una solicitud de compra',
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
