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
            'id'                  => ['nullable', 'integer'],
            'agencia'              => ['nullable'],
            'com_vendedores_id'   => ['required', 'integer'],
            'folio'               => ['required', 'string', 'max:100'],
            'poliza'              => ['required', 'string', 'max:100'],
            'fecha_emision'       => ['required', 'date'],
            'prima_neta'          => ['required', 'numeric', 'min:0'],
            'comision_apv_pesos'  => ['required', 'numeric', 'min:0'],
            'observaciones'       => ['nullable', 'string'],
            'comentario'          => ['nullable', 'string'],
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
            'com_vendedores_id.required' => 'El vendedor es obligatorio',
            'com_vendedores_id.exists'   => 'El vendedor no existe',

            'folio.required' => 'El folio es obligatorio',
            'poliza.required' => 'La póliza es obligatoria',

            'fecha_emision.required' => 'La fecha de emisión es obligatoria',
            'fecha_emision.date'     => 'Formato de fecha inválido',

            'prima_neta.required' => 'La prima neta es obligatoria',
            'prima_neta.numeric'  => 'Debe ser un número válido',

            'comision_apv_pesos.required' => 'La comisión es obligatoria',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'prima_neta' => $this->limpiarNumero($this->prima_neta),
            'comision_apv_pesos' => $this->limpiarNumero($this->comision_apv_pesos),
        ]);
    }

    private function limpiarNumero($valor)
    {
        if (!$valor) return 0;

        return floatval(str_replace([',', '$', ' '], '', $valor));
    }
}
