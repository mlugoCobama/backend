<?php

namespace Modules\Compras\Http\Requests;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Foundation\Http\FormRequest;

class StoreSolicitudCompraRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'data.usuario_solicita' => 'required|integer',
            'data.usuario_destino' => 'required|integer',
            'data.motivo' => 'required|string',
            'data.c_c' => 'nullable||integer',
            'data.detalles' => 'required|array|min:1',
            'data.detalles.*.cantidad' => 'required|numeric|min:1',
            'data.detalles.*.descripcion' => 'required|string',
            'data.detalles.*.observaciones' => 'nullable|string',
            'data.detalles.*.cat_unidades_medida_id' => 'required|integer',
            'img_referencia_*' => 'nullable|file|mimes:jpg,jpeg,png|max:51200'
        ];
    }

    public function validationData()
    {
        $data =  json_decode($this->input('data'), true) ?? [];
        return array_merge($this->all(), ['data'=> $data]);
    }

    public function messages()
    {
        return[
            'data.usuario_solicita.required' => 'Usuario Solicita es obligatorio',
            // 'data.usuario_destino.required' => 'Usuario Destino es obligatorio',
            'data.motivo.required' => 'Motivo es obligatorio',
            'data.detalles.required' => 'Los detalles son obligatorios',
            'data.detalles.*.cantidad.min' => 'La cantidad minima es 1',
            'data.detalles.*.descripcion.required' => 'Agrega una descripción',
            'data.detalles.*.cat_unidades_medida_id.required' => 'Agrega una unidad de medida',
            'img_referencia_*.mimes' => 'La imagen debe de ser de tipo jpg,jpeg o png',
            'img_referencia_*.max' => 'EL archivo es muy grande (Máximo 50 MB)'
        ];
    }

    public function failedValidator( ValidatorContract $validator ){
        $response = response()->json([
                       'status' => 'error',
                       'message' => 'Datos no validos',
                       'errors' => $validator->errors()
                   ]);
        throw new HttpResponseException($response);
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }


}
