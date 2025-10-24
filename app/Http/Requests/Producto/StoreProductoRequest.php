<?php
declare(strict_types=1);

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreProductoRequest extends FormRequest
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
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|unique:productos,codigo|max:50',
            'precio_unitario' => 'required|numeric|min:0|max:9999999.99',
            'descripcion' => 'nullable|string|max:1000',
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
            'nombre.required' => 'El nombre del producto es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres',
            'codigo.required' => 'El código del producto es obligatorio',
            'codigo.unique' => 'Este código ya está registrado',
            'codigo.max' => 'El código no puede exceder 50 caracteres',
            'precio_unitario.required' => 'El precio unitario es obligatorio',
            'precio_unitario.numeric' => 'El precio unitario debe ser un número',
            'precio_unitario.min' => 'El precio unitario debe ser mayor o igual a 0',
            'precio_unitario.max' => 'El precio unitario excede el límite permitido',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres',
        ];
    }
}
