<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Producto;
use App\Services\ProductoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProductoService::class);
    }

    public function test_can_create_producto(): void
    {
        $data = [
            'nombre' => 'Test Producto',
            'codigo' => 'TEST001',
            'precio_unitario' => 100.50,
            'descripcion' => 'Test description',
        ];

        $producto = $this->service->create($data);

        $this->assertInstanceOf(Producto::class, $producto);
        $this->assertEquals('Test Producto', $producto->nombre);
        $this->assertEquals('TEST001', $producto->codigo);
        $this->assertEquals(100.50, (float) $producto->precio_unitario);
    }

    public function test_can_find_producto_by_id(): void
    {
        $producto = Producto::factory()->create();

        $found = $this->service->findById($producto->id);

        $this->assertNotNull($found);
        $this->assertEquals($producto->id, $found->id);
    }

    public function test_can_find_producto_by_codigo(): void
    {
        $producto = Producto::factory()->create(['codigo' => 'UNIQUE001']);

        $found = $this->service->findByCodigo('UNIQUE001');

        $this->assertNotNull($found);
        $this->assertEquals('UNIQUE001', $found->codigo);
    }

    public function test_can_update_producto(): void
    {
        $producto = Producto::factory()->create();

        $updated = $this->service->update($producto, [
            'nombre' => 'Updated Product',
            'precio_unitario' => 200.00,
        ]);

        $this->assertEquals('Updated Product', $updated->nombre);
        $this->assertEquals(200.00, (float) $updated->precio_unitario);
    }

    public function test_can_delete_producto(): void
    {
        $producto = Producto::factory()->create();

        $result = $this->service->delete($producto);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('productos', ['id' => $producto->id]);
    }

    public function test_can_search_productos(): void
    {
        Producto::factory()->create(['nombre' => 'Laptop Dell']);
        Producto::factory()->create(['nombre' => 'Mouse Logitech']);

        $results = $this->service->search('Laptop');

        $this->assertGreaterThan(0, $results->total());
    }
}
