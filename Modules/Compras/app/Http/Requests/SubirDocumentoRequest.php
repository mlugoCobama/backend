<?php

namespace Modules\Compras\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubirDocumentoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'tipo_documento'   => 'required|string',
            'orden_compra_id'  => 'required|integer',
            'idFactura'        => 'required|integer',
            'archivo_xml'      => 'nullable|file|mimes:xml,application/xml|max:10240',
            'archivo'          => 'required|file|mimes:pdf,jpeg,png|max:20480',
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
            'archivo_xml.mimes'    => 'El archivo XML debe tener extensión .xml.',
            'archivo.required'     => 'La representación impresa es obligatoria.',
            'archivo.mimes'        => 'La representación impresa debe ser PDF, JPEG o PNG.',
        ];
    }
}
