<?php

namespace Modules\Nissan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTomaUnidadRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {

    return [
            'id'                    => 'nullable|integer',
            'agencia'                    => 'nullable',
            'fecha_toma'            => 'required|date',
            'clave_producto'        => 'required|string',
            'no_inventario'         => 'required|string|max:255',
            'comision_apv_pesos'    => 'required|numeric|min:0',
            'com_vendedores_id'     => 'required|integer',
            'observaciones'         => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [

            'fecha_toma.required'       => 'La fecha de desembolso es obligatoria',
            'fecha_toma.date'           => 'La fecha no tiene un formato válido',
            'no_inventario.required'         => 'El número de factura es obligatorio',
            'clave_producto.required'         => 'El número de factura es obligatorio',
            'comision_apv_pesos.required'  => 'La comisión es obligatoria',
            'com_vendedores_id.required'      => 'Debe seleccionar un vendedor',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'comision_apv_pesos' => $this->limpiarNumero($this->comision_apv_pesos),
        ]);
    }

    private function limpiarNumero($valor): float|null
    {
        if (is_null($valor)) return null;
        return (float) str_replace(',', '', $valor);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
