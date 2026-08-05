<?php

namespace Modules\Ucoip\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TokensAgenciaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'token' => ['required'. ($this->input('id') ? $this->input('id') : null)],
            'puesto_marca' => ['required'],
            'cat_empresas_id' => ['required'],
            'observaciones' => ['nullable', 'string']
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'token.required' => 'El token es requerido',
            'token.unique' => 'Este token ya existe en otra sucursal',
            'puesto_marca.required' => 'El puesto de marca es requerido',
            'cat_empresas_id.required' => 'La empresas es requerida'
        ];
    }
}
