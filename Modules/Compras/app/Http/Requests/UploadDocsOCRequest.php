<?php

namespace Modules\Compras\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocsOCRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
          'orden_compra_id' => 'nullable|exists:com_orden_compra,id',
          'factura_xml' => 'nullable|file',
          'factura_pdf' => 'nullable|file|mimes:pdf',
          'complemento_pago_xml' => 'nullable|file',
          'complemento_pago_pdf' => 'nullable|file|mimes:pdf',
          'comprobante_pago' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
          'total_compra' => 'nullable',
          'suma_facturas' => 'nullable',
        ];
    }

    public function messages()
    {
        return[
            'factura_xml.mimes' => "El archivo debe de ser un XML",
            'factura_xml.max' => "El archivo Es muy grande (Máximo 20 MB)",
            'factura_pdf.mimes' => "El archivo debe de ser un pdf",
            'factura_pdf.max' => "El archivo Es muy grande (Máximo 20 MB)",
            'comprobante_pago.mimes' => "El archivo debe de ser un pdf, jpg o png",
            'comprobante_pago.max' => "El archivo Es muy grande (Máximo 20 MB)",
            'complemento_pago_xml.mimes' => "El archivo debe de ser un XML",
            'complemento_pago_xml.max' => "El archivo Es muy grande (Máximo 20 MB)",
            'complemento_pago_pdf.mimes' => "El archivo debe de ser un XML",
            'complemento_pago_pdf.max' => "El archivo Es muy grande (Máximo 20 MB)",
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
