<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Consecutivo;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario de prueba
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Crear consecutivo
        $consecutivo = Consecutivo::create([
            'prefijo' => 'FAC',
            'numero_actual' => 1,
        ]);

        // Crear productos
        $productos = [
            [
                'nombre' => 'Laptop Dell XPS 15',
                'codigo' => 'PROD001',
                'precio_unitario' => 1500.00,
                'descripcion' => 'Laptop de alto rendimiento',
            ],
            [
                'nombre' => 'Mouse Logitech MX Master',
                'codigo' => 'PROD002',
                'precio_unitario' => 89.99,
                'descripcion' => 'Mouse inalámbrico ergonómico',
            ],
            [
                'nombre' => 'Teclado Mecánico RGB',
                'codigo' => 'PROD003',
                'precio_unitario' => 120.00,
                'descripcion' => 'Teclado mecánico con iluminación RGB',
            ],
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }

        // Crear clientes
        $clientes = [
            [
                'nombre' => 'Juan Pérez',
                'email' => 'juan@example.com',
                'telefono' => '555-0101',
                'direccion' => 'Calle 123 #45-67',
                'identificacion' => '1234567890',
            ],
            [
                'nombre' => 'María García',
                'email' => 'maria@example.com',
                'telefono' => '555-0102',
                'direccion' => 'Avenida 456 #78-90',
                'identificacion' => '0987654321',
            ],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }

        // Crear factura de ejemplo
        $cliente = Cliente::first();
        $producto1 = Producto::where('codigo', 'PROD001')->first();
        $producto2 = Producto::where('codigo', 'PROD002')->first();

        $factura = Factura::create([
            'cliente_id' => $cliente->id,
            'consecutivo_id' => $consecutivo->id,
            'numero_factura' => $consecutivo->obtenerSiguienteNumero(),
            'fecha_emision' => now(),
            'total' => 0,
        ]);

        // Crear detalles de factura
        FacturaDetalle::create([
            'factura_id' => $factura->id,
            'producto_id' => $producto1->id,
            'cantidad' => 1,
            'precio_unitario' => $producto1->precio_unitario,
            'subtotal' => 1 * $producto1->precio_unitario,
        ]);

        FacturaDetalle::create([
            'factura_id' => $factura->id,
            'producto_id' => $producto2->id,
            'cantidad' => 2,
            'precio_unitario' => $producto2->precio_unitario,
            'subtotal' => 2 * $producto2->precio_unitario,
        ]);

        // Calcular total de la factura
        $factura->calcularTotal();
    }
}
