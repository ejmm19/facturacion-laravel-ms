<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Factura;
use App\Models\Cliente;
use App\Models\Consecutivo;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacturaFactory extends Factory
{
    protected $model = Factura::class;

    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'consecutivo_id' => Consecutivo::factory(),
            'numero_factura' => fake()->unique()->numerify('FAC######'),
            'fecha_emision' => fake()->date(),
            'total' => fake()->randomFloat(2, 100, 10000),
        ];
    }
}
