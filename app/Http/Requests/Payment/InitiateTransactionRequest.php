<?php

namespace App\Http\Requests\Payment;

use App\Traits\ApiResponser;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class InitiateTransactionRequest extends FormRequest
{
    use ApiResponser;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monto' => 'required|integer|min:1',
            'dispositivo_id' => 'required|integer',
            'origen_id' => 'required|integer',
            'banco_id' => 'required|integer',
            'propina' => 'nullable|integer|min:0',
            'qr_id' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'monto.required' => 'El monto es requerido',
            'monto.integer' => 'El monto debe ser un entero',
            'monto.min' => 'El monto debe ser mayor a 0',
            'dispositivo_id.required' => 'El dispositivo es requerido',
            'dispositivo_id.integer' => 'El dispositivo debe ser un identificador válido',
            'origen_id.required' => 'El origen es requerido',
            'origen_id.integer' => 'El origen debe ser un identificador válido',
            'banco_id.required' => 'El banco es requerido',
            'banco_id.integer' => 'El banco debe ser un identificador válido',
            'propina.integer' => 'La propina debe ser un entero',
            'propina.min' => 'La propina no puede ser negativa',
            'qr_id.required' => 'El ID del QR es requerido',
            'qr_id.string' => 'El ID del QR debe ser una cadena de texto',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->requestResponse($validator));
    }
}
