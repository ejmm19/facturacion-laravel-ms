<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\FacturaDetalle;
use App\Models\Factura;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacturaDetalleFactory extends Factory
{
    protected $model = FacturaDetalle::class;

    public function definition(): array
    {
        $cantidad = fake()->numberBetween(1, 10);
        $precioUnitario = fake()->randomFloat(2, 10, 1000);
        
        return [
            'factura_id' => Factura::factory(),
            'producto_id' => Producto::factory(),
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'subtotal' => $cantidad * $precioUnitario,
        ];
    }
}
