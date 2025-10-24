<?php
declare(strict_types=1);

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $clienteId = $this->route('cliente');
        
        return [
            'nombre' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('clientes', 'email')->ignore($clienteId),
            ],
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'identificacion' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('clientes', 'identificacion')->ignore($clienteId),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe ser válido',
            'email.unique' => 'Este email ya está registrado',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres',
            'direccion.max' => 'La dirección no puede exceder 500 caracteres',
            'identificacion.required' => 'La identificación es obligatoria',
            'identificacion.unique' => 'Esta identificación ya está registrada',
            'identificacion.max' => 'La identificación no puede exceder 50 caracteres',
        ];
    }
}
