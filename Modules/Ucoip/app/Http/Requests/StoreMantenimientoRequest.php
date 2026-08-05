<?php

namespace Modules\Ucoip\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMantenimientoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'data' => ['required', 'string'],
            'evidencia_antes.*' => ['nullable', 'file', 'image', 'max:5120'],
            'evidencia_despues.*' => ['nullable', 'file', 'image', 'max:5120'],
        ];
    }

    /**
     * Decodifica y valida el JSON que viene dentro de 'data'.
     */
    public function payload(): array
    {
        $decoded = json_decode($this->input('data'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            abort(422, 'JSON inválido en el campo data');
        }

        return $decoded;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
