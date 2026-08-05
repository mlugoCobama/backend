<?php

namespace Modules\Ucoip\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SoftwareRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Adjust authorization logic as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $id = $this->input('id');
        if ($id) {
            return [
                'empresa' => ['required', 'string'],
                'version' => ['required', 'string'],
                'licencia' => ['required', 'string'],
                'observaciones' => ['nullable', 'string'],
                'cat_software_id' => ['required', 'integer'],
                'estatus' => ['nullable'],
                'tipo_licencia' => ['nullable', 'string'],
                'cuenta' => ['nullable', 'string'],
                'pass_cuenta' => ['nullable', 'string'],
                'fecha_adquisicion' => ['nullable', 'date'],
            ];
        } else {
            return [
                'empresa' => ['required', 'string'],
                'version' => ['required', 'string'],
                'licencia' => ['required', 'string'],
                'observaciones' => ['nullable', 'string'],
                'cat_software_id' => ['required', 'integer'],
                'estatus' => ['nullable'],
                'tipo_licencia' => ['nullable', 'string'],
                'cuenta' => ['nullable', 'string'],
                'pass_cuenta' => ['nullable', 'string'],
                'fecha_adquisicion' => ['nullable', 'date'],
            ];
        }
    }


    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'empresa.required' => 'El campo empresa es obligatorio.',
            'cat_software_id.required' => 'El campo tipo de software es obligatorio.',
            'version.required' => 'El campo version es obligatorio.',
            'licencia.required' => 'El campo licnecia es obligatorio'
        ];
    }

}
