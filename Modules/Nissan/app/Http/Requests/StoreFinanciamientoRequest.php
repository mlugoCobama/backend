<?php
namespace Modules\Nissan\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreFinanciamientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'financiamientos'                              => 'required|array|min:1',
            'financiamientos.*.id'                        => 'nullable',
            'financiamientos.*.agencia'                   => 'nullable',
            'financiamientos.*.no_contrato'               => 'required|string|max:255',
            'financiamientos.*.fecha_desembolso'          => 'required|date',
            'financiamientos.*.numero_factura'            => 'required|string|max:255',
            'financiamientos.*.monto_financiar'           => 'required|numeric|min:0',
            'financiamientos.*.incentivo_dealer'          => 'required|numeric|min:0',
            'financiamientos.*.porcentaje_asesor'         => 'required|numeric|min:0|max:100',
            'financiamientos.*.comision_asesor_pesos'     => 'required|numeric|min:0',
            'financiamientos.*.com_vendedores_id'         => 'required|integer',
            'financiamientos.*.com_datos_venta_id'        => 'nullable|integer',
            'financiamientos.*.tipo_financiamiento'       => 'nullable|string|max:255',
            'financiamientos.*.archivo'                   => 'nullable',
            'financiamientos.*.observaciones'             => 'nullable|string',
            'financiamientos.*.kit_seguridad'             => 'nullable|numeric|min:0',
            'financiamientos.*.sat_finder'                => 'nullable|numeric|min:0',
            'financiamientos.*.garantia_extendida'        => 'nullable|numeric|min:0',
            'financiamientos.*.seguro_vf3'                => 'nullable|numeric|min:0',
            'financiamientos.*.accesorios_adicionales'    => 'nullable|numeric|min:0',
            'financiamientos.*.comision_mantenimiento'    => 'nullable|numeric|min:0',
            'financiamientos.*.comision_garantia_extendida' => 'nullable|numeric|min:0',
            'financiamientos.*.comision_udi'              => 'nullable|numeric|min:0',
            'financiamientos.*.comision_vf3'              => 'nullable|numeric|min:0',
            'financiamientos.*.sub_x_des'                 => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'financiamientos.required'                          => 'Debe agregar al menos un financiamiento',
            'financiamientos.min'                               => 'Debe agregar al menos un financiamiento',
            'financiamientos.*.no_contrato.required'            => 'El número de contrato es obligatorio',
            'financiamientos.*.fecha_desembolso.required'       => 'La fecha de desembolso es obligatoria',
            'financiamientos.*.fecha_desembolso.date'           => 'La fecha no tiene un formato válido',
            'financiamientos.*.numero_factura.required'         => 'El número de factura es obligatorio',
            'financiamientos.*.monto_financiar.required'        => 'El monto a financiar es obligatorio',
            'financiamientos.*.monto_financiar.numeric'         => 'El monto debe ser un número',
            'financiamientos.*.incentivo_dealer.required'       => 'El incentivo dealer es obligatorio',
            'financiamientos.*.porcentaje_asesor.required'      => 'El porcentaje del asesor es obligatorio',
            'financiamientos.*.porcentaje_asesor.max'           => 'El porcentaje no puede ser mayor a 100',
            'financiamientos.*.comision_asesor_pesos.required'  => 'La comisión es obligatoria',
            'financiamientos.*.com_vendedores_id.required'      => 'Debe seleccionar un vendedor',
            'financiamientos.*.archivo.mimes'                   => 'El archivo debe ser PDF, JPG o PNG',
            'financiamientos.*.archivo.max'                     => 'El archivo no debe superar 5MB',
        ];
    }

    protected function prepareForValidation(): void
    {
        $financiamientos = $this->financiamientos;

        if (!is_array($financiamientos)) return;

        $financiamientos = array_map(function ($item) {
            // solo limpiamos los campos numéricos de texto
            return array_merge($item, [
                'id'                        => $item['id'] === 'null' ? null : $item['id'],
                'monto_financiar'           => $this->limpiarNumero($item['monto_financiar']           ?? null),
                'incentivo_dealer'          => $this->limpiarNumero($item['incentivo_dealer']          ?? null),
                'porcentaje_asesor'         => $this->limpiarNumero($item['porcentaje_asesor']         ?? null),
                'comision_asesor_pesos'     => $this->limpiarNumero($item['comision_asesor_pesos']     ?? null),
                'kit_seguridad'             => $this->limpiarNumero($item['kit_seguridad']             ?? null),
                'sat_finder'                => $this->limpiarNumero($item['sat_finder']                ?? null),
                'garantia_extendida'        => $this->limpiarNumero($item['garantia_extendida']        ?? null),
                'seguro_vf3'               => $this->limpiarNumero($item['seguro_vf3']                ?? null),
                'accesorios_adicionales'    => $this->limpiarNumero($item['accesorios_adicionales']    ?? null),
                'comision_mantenimiento'    => $this->limpiarNumero($item['comision_mantenimiento']    ?? null),
                'comision_garantia_extendida' => $this->limpiarNumero($item['comision_garantia_extendida'] ?? null),
                'comision_udi'              => $this->limpiarNumero($item['comision_udi']              ?? null),
                'comision_vf3'              => $this->limpiarNumero($item['comision_vf3']              ?? null),
                'sub_x_des'                 => $this->limpiarNumero($item['sub_x_des']                ?? null),
            ]);
        }, $financiamientos);

        $this->merge(['financiamientos' => $financiamientos]);
    }

    private function limpiarNumero($valor): float|null
    {
        if (is_null($valor) || $valor === '') return null;
        return (float) str_replace(',', '', $valor);
    }
}