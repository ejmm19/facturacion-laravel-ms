<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->words(3, true),
            'codigo' => fake()->unique()->bothify('PROD###'),
            'precio_unitario' => fake()->randomFloat(2, 10, 5000),
            'descripcion' => fake()->sentence(),
        ];
    }
}
