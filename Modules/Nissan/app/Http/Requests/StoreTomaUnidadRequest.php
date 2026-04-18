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
            'toma_unidad'                         => 'required|array|min:1',
            'toma_unidad.*.agencia'               => 'nullable',
            'toma_unidad.*.com_vendedores_id'     => 'required|integer',
            'toma_unidad.*.id'                    => 'nullable',
            'toma_unidad.*.fecha_toma'            => 'required|date',
            'toma_unidad.*.vehiculo'              => 'required|string',
            'toma_unidad.*.numero_serie'          => 'required|string',
            'toma_unidad.*.tipo_apv'              => 'required|string',
            'toma_unidad.*.por_inventario'        => 'required|string|max:255',
            'toma_unidad.*.comision_apv_pesos'    => 'required|numeric|min:0',
            'toma_unidad.*.observaciones'         => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'toma_unidad.required'                => 'Debe agregar al menos una toma de unidad',
            'toma_unidad.min'                     => 'Debe agregar al menos una toma de unidad',
            'toma_unidad.*.fecha_toma.required'       => 'La fecha de desembolso es obligatoria',
            'toma_unidad.*.fecha_toma.date'           => 'La fecha no tiene un formato válido',
            'toma_unidad.*.por_inventario.required'         => 'El número de factura es obligatorio',
            'toma_unidad.*.tipo_apv.required'         => 'El número de factura es obligatorio',
            'toma_unidad.*.comision_apv_pesos.required'  => 'La comisión es obligatoria',
            'toma_unidad.*.com_vendedores_id.required'      => 'Debe seleccionar un vendedor',
        ];
    }

    protected function prepareForValidation(): void
    {
        $tomaUnidad = $this->toma_unidad;

        if (!is_array($tomaUnidad)) return;

        $tomaUnidad = array_map(function ($item) {
            return array_merge($item, [
                'id'                        => $item['id'] === 'null' ? null : $item['id'],
                'comision_apv_pesos' => $this->limpiarNumero($item['comision_apv_pesos'] ?? null),
                'observaciones' => ($item['observaciones'] === 'null' ? '': $item['observaciones']),
            ]);
        }, $tomaUnidad);

        $this->merge(['toma_unidad' => $tomaUnidad]);
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
