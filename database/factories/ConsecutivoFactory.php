<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Consecutivo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsecutivoFactory extends Factory
{
    protected $model = Consecutivo::class;

    public function definition(): array
    {
        return [
            'prefijo' => fake()->unique()->lexify('???'),
            'numero_actual' => 1,
        ];
    }
}
