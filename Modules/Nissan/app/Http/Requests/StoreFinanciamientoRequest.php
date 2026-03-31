<?php

namespace Modules\Nissan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinanciamientoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function authorize(): bool
    {
        return true; // Cambia a lógica de roles si necesitas
    }

    public function rules(): array
    {
        return [
            'id'                    => 'nullable|integer',
            'no_contrato'           => 'required|string|max:255',
            'fecha_desembolso'      => 'required|date',
            'numero_factura'        => 'required|string|max:255',
            'monto_financiar'       => 'required|numeric|min:0',
            'incentivo_dealer'      => 'required|numeric|min:0',
            'porcentaje_asesor'     => 'required|numeric|min:0|max:100',
            'comision_asesor_pesos' => 'required|numeric|min:0',
            'com_vendedores_id'     => 'required|integer',
            'com_datos_venta_id'    => 'nullable|integer',
            'tipo_financiamiento'   => 'nullable|string|max:255',
            'archivo'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'observaciones'         => 'nullable|string',
            
        ];
    }

    public function messages(): array
    {
        return [
            'no_contrato.required'           => 'El número de contrato es obligatorio',
            'fecha_desembolso.required'       => 'La fecha de desembolso es obligatoria',
            'fecha_desembolso.date'           => 'La fecha no tiene un formato válido',
            'numero_factura.required'         => 'El número de factura es obligatorio',
            'monto_financiar.required'        => 'El monto a financiar es obligatorio',
            'monto_financiar.numeric'         => 'El monto debe ser un número',
            'incentivo_dealer.required'       => 'El incentivo dealer es obligatorio',
            'porcentaje_asesor.required'      => 'El porcentaje del asesor es obligatorio',
            'porcentaje_asesor.max'           => 'El porcentaje no puede ser mayor a 100',
            'comision_asesor_pesos.required'  => 'La comisión es obligatoria',
            'com_vendedores_id.required'      => 'Debe seleccionar un vendedor',
            'com_vendedores_id.exists'        => 'El vendedor seleccionado no existe',
            'archivo.mimes'                   => 'El archivo debe ser PDF, JPG o PNG',
            'archivo.max'                     => 'El archivo no debe superar 5MB',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'monto_financiar'       => $this->limpiarNumero($this->monto_financiar),
            'incentivo_dealer'      => $this->limpiarNumero($this->incentivo_dealer),
            'porcentaje_asesor'     => $this->limpiarNumero($this->porcentaje_asesor),
            'comision_asesor_pesos' => $this->limpiarNumero($this->comision_asesor_pesos),
        ]);
    }

    private function limpiarNumero($valor): float|null
    {
        if (is_null($valor)) return null;
        return (float) str_replace(',', '', $valor);
    }
}
