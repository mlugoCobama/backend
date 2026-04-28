<?php

namespace Modules\Nissan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeguroRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
            return [
            // identificadores
            'seguros'  => 'required|array|min:1',
            'seguros.*.id' => ['nullable', 'integer'],
            'seguros.*.agencia' => ['nullable'],
            'seguros.*.com_vendedores_id' => ['required', 'integer'],
            // básicos
            'seguros.*.folio' => ['required', 'string', 'max:100'],
            'seguros.*.poliza' => ['required', 'string', 'max:100'],
            'seguros.*.aseguradora' => ['required', 'string', 'max:150'],
            'seguros.*.nombre' => ['required', 'string', 'max:150'],
            'seguros.*.unidad' => ['required', 'string', 'max:150'],
            'seguros.*.serie' => ['required', 'string', 'max:100'],
            'seguros.*.archivo' => 'nullable',    
            // fechas
            'seguros.*.fecha_emision' => ['required', 'date'],
            // info adicional
            'seguros.*.forma_pago' => ['required', 'string', 'max:100'],
            // montos
            'seguros.*.prima_neta' => ['required', 'numeric', 'min:0'],
            'seguros.*.vs' => ['nullable', 'numeric', 'min:0'],
            'seguros.*.calcular_encargado_seg' => ['required', 'boolean'],
            'seguros.*.com_encargado_seg' => ['nullable', 'numeric', 'min:0'],
            // comisión
            'seguros.*.comision_apv_pesos' => ['nullable', 'numeric', 'min:0'],
            // extras
            'seguros.*.observaciones' => ['nullable', 'string'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'seguros.required' => 'Debe agregar al menos un seguro',
            'seguros.min'      => 'Debe agregar al menos un seguro',

            'seguros.*.com_vendedores_id.required' => 'El vendedor es obligatorio',
            'seguros.*.com_vendedores_id.exists'   => 'El vendedor no existe',

            'seguros.*.folio.required' => 'El folio es obligatorio',
            'seguros.*.poliza.required' => 'La póliza es obligatoria',

            'seguros.*.fecha_emision.required' => 'La fecha de emisión es obligatoria',
            'seguros.*.fecha_emision.date'     => 'Formato de fecha inválido',

            'seguros.*.prima_neta.required' => 'La prima neta es obligatoria',
            'seguros.*.prima_neta.numeric'  => 'Debe ser un número válido',

            'seguros.*.comision_apv_pesos.required' => 'La comisión es obligatoria',
        ];
    }

    protected function prepareForValidation()
    {
        $seguros = $this->seguros;

        if (!is_array($seguros)) return;

        $seguros = array_map(function ($item) {

            return array_merge($item, [
                'id'                        => $item['id'] === 'null' ? null : $item['id'],
                'prima_neta' => $this->limpiarNumero($item['prima_neta'] ?? 0),
                'comision_apv_pesos' => $this->limpiarNumero($item['comision_apv_pesos'] ?? 0),
                'calcular_encargado_seg' => filter_var($item['calcular_encargado_seg'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'observaciones' => ($item['observaciones'] === 'null' ? '': $item['observaciones']),
            ]);

        }, $seguros);

        $this->merge([
            'seguros' => $seguros
        ]);
    }



    private function limpiarNumero($valor)
    {
        if (!$valor) return 0;

        return floatval(str_replace([',', '$', ' '], '', $valor));
    }
}
