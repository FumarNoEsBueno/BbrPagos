<?php

namespace App\Http\Requests\Payment;

use App\Traits\ApiResponser;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class TransitionStateRequest extends FormRequest
{
    use ApiResponser;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nuevo_estado' => [
                'required',
                'string',
                Rule::in(['pendiente', 'procesando', 'aprobado', 'rechazado', 'anulado']),
            ],
            'motivo' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'nuevo_estado.required' => 'El nuevo estado es requerido',
            'nuevo_estado.string' => 'El estado debe ser una cadena de texto',
            'nuevo_estado.in' => 'El estado debe ser uno de los siguientes: pendiente, procesando, aprobado, rechazado, anulado',
            'motivo.string' => 'El motivo debe ser una cadena de texto',
            'motivo.max' => 'El motivo no puede superar los 500 caracteres',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->requestResponse($validator));
    }
}
