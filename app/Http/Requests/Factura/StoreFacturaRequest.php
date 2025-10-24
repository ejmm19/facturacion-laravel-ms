<?php
declare(strict_types=1);

namespace App\Http\Requests\Factura;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreFacturaRequest extends FormRequest
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
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'consecutivo_id' => 'required|exists:consecutivos,id',
            'fecha_emision' => 'required|date',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
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
            'cliente_id.required' => 'El cliente es obligatorio',
            'cliente_id.exists' => 'El cliente seleccionado no existe',
            'consecutivo_id.required' => 'El consecutivo es obligatorio',
            'consecutivo_id.exists' => 'El consecutivo seleccionado no existe',
            'fecha_emision.required' => 'La fecha de emisión es obligatoria',
            'fecha_emision.date' => 'La fecha de emisión debe ser válida',
            'detalles.required' => 'Debe agregar al menos un detalle',
            'detalles.array' => 'Los detalles deben ser un array',
            'detalles.min' => 'Debe agregar al menos un producto',
            'detalles.*.producto_id.required' => 'El producto es obligatorio',
            'detalles.*.producto_id.exists' => 'El producto seleccionado no existe',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria',
            'detalles.*.cantidad.integer' => 'La cantidad debe ser un número entero',
            'detalles.*.cantidad.min' => 'La cantidad debe ser al menos 1',
            'detalles.*.precio_unitario.required' => 'El precio unitario es obligatorio',
            'detalles.*.precio_unitario.numeric' => 'El precio unitario debe ser un número',
            'detalles.*.precio_unitario.min' => 'El precio unitario debe ser mayor o igual a 0',
        ];
    }
}
