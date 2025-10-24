<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Factura;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Consecutivo;
use App\Services\FacturaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacturaServiceTest extends TestCase
{
    use RefreshDatabase;

    private FacturaService $service;
    private Cliente $cliente;
    private Producto $producto;
    private Consecutivo $consecutivo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(FacturaService::class);
        $this->cliente = Cliente::factory()->create();
        $this->producto = Producto::factory()->create(['precio_unitario' => 100.00]);
        $this->consecutivo = Consecutivo::factory()->create();
    }

    public function test_can_create_factura_with_detalles(): void
    {
        $data = [
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
            'fecha_emision' => now()->format('Y-m-d'),
            'detalles' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 2,
                    'precio_unitario' => 100.00,
                ]
            ]
        ];

        $factura = $this->service->create($data);

        $this->assertInstanceOf(Factura::class, $factura);
        $this->assertEquals(200.00, (float) $factura->total);
        $this->assertEquals(1, $factura->detalles->count());
    }

    public function test_factura_generates_numero_correctly(): void
    {
        $data = [
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
            'fecha_emision' => now()->format('Y-m-d'),
            'detalles' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 1,
                    'precio_unitario' => 100.00,
                ]
            ]
        ];

        $factura = $this->service->create($data);

        $this->assertStringStartsWith($this->consecutivo->prefijo, $factura->numero_factura);
    }

    public function test_can_find_factura_by_id(): void
    {
        $factura = Factura::factory()->create([
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
        ]);

        $found = $this->service->findById($factura->id);

        $this->assertNotNull($found);
        $this->assertEquals($factura->id, $found->id);
    }

    public function test_can_find_factura_by_numero(): void
    {
        $factura = Factura::factory()->create([
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
            'numero_factura' => 'FAC000123',
        ]);

        $found = $this->service->findByNumero('FAC000123');

        $this->assertNotNull($found);
        $this->assertEquals('FAC000123', $found->numero_factura);
    }

    public function test_can_get_facturas_by_cliente(): void
    {
        Factura::factory()->count(3)->create([
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
        ]);

        $results = $this->service->getByCliente($this->cliente->id);

        $this->assertEquals(3, $results->total());
    }

    public function test_can_get_estadisticas(): void
    {
        Factura::factory()->count(5)->create([
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
            'total' => 1000.00,
        ]);

        $stats = $this->service->getEstadisticas();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_facturas', $stats);
        $this->assertArrayHasKey('total_ventas', $stats);
        $this->assertEquals(5, $stats['total_facturas']);
    }

    public function test_can_delete_factura(): void
    {
        $factura = Factura::factory()->create([
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
        ]);

        $result = $this->service->delete($factura);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('facturas', ['id' => $factura->id]);
    }
}
