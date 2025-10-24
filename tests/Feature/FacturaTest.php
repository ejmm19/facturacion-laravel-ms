<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Consecutivo;
use App\Models\Factura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacturaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Cliente $cliente;
    private Producto $producto;
    private Consecutivo $consecutivo;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        $this->cliente = Cliente::factory()->create();
        $this->producto = Producto::factory()->create();
        $this->consecutivo = Consecutivo::factory()->create();
    }

    public function test_can_list_facturas(): void
    {
        Factura::factory()->count(3)->create([
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson('/api/facturas');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'numero_factura', 'total', 'fecha_emision']
                     ]
                 ]);
    }

    public function test_can_create_factura_with_detalles(): void
    {
        $facturaData = [
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
            'fecha_emision' => now()->format('Y-m-d'),
            'detalles' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 2,
                    'precio_unitario' => $this->producto->precio_unitario,
                ]
            ]
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->postJson('/api/facturas', $facturaData);

        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Factura creada exitosamente',
                 ])
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'numero_factura',
                         'total',
                         'detalles'
                     ]
                 ]);

        $this->assertDatabaseHas('facturas', [
            'cliente_id' => $this->cliente->id,
        ]);

        $this->assertDatabaseHas('factura_detalles', [
            'producto_id' => $this->producto->id,
            'cantidad' => 2,
        ]);
    }

    public function test_factura_calculates_total_correctly(): void
    {
        $producto1 = Producto::factory()->create(['precio_unitario' => 100.00]);
        $producto2 = Producto::factory()->create(['precio_unitario' => 50.00]);

        $facturaData = [
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
            'fecha_emision' => now()->format('Y-m-d'),
            'detalles' => [
                [
                    'producto_id' => $producto1->id,
                    'cantidad' => 2,
                    'precio_unitario' => 100.00,
                ],
                [
                    'producto_id' => $producto2->id,
                    'cantidad' => 3,
                    'precio_unitario' => 50.00,
                ]
            ]
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->postJson('/api/facturas', $facturaData);

        $response->assertStatus(201);

        $expectedTotal = (2 * 100.00) + (3 * 50.00); // 350.00

        $this->assertDatabaseHas('facturas', [
            'cliente_id' => $this->cliente->id,
            'total' => $expectedTotal,
        ]);
    }

    public function test_cannot_create_factura_without_detalles(): void
    {
        $facturaData = [
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
            'fecha_emision' => now()->format('Y-m-d'),
            'detalles' => []
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->postJson('/api/facturas', $facturaData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['detalles']);
    }

    public function test_cannot_create_factura_with_nonexistent_cliente(): void
    {
        $facturaData = [
            'cliente_id' => 99999,
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

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->postJson('/api/facturas', $facturaData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['cliente_id']);
    }

    public function test_can_show_factura_with_detalles(): void
    {
        $factura = Factura::factory()
            ->hasDetalles(2)
            ->create([
                'cliente_id' => $this->cliente->id,
                'consecutivo_id' => $this->consecutivo->id,
            ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson("/api/facturas/{$factura->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'numero_factura',
                         'cliente',
                         'detalles' => [
                             '*' => ['id', 'producto', 'cantidad', 'precio_unitario', 'subtotal']
                         ]
                     ]
                 ]);
    }

    public function test_can_filter_facturas_by_cliente(): void
    {
        $otroCliente = Cliente::factory()->create();
        
        Factura::factory()->create([
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
        ]);
        
        Factura::factory()->create([
            'cliente_id' => $otroCliente->id,
            'consecutivo_id' => $this->consecutivo->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson("/api/facturas?cliente_id={$this->cliente->id}");

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    public function test_can_get_estadisticas(): void
    {
        Factura::factory()->count(5)->create([
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
            'total' => 1000.00,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson('/api/facturas/estadisticas');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'total_facturas',
                         'total_ventas',
                         'promedio_venta',
                         'facturas_hoy',
                         'ventas_hoy',
                         'facturas_mes',
                         'ventas_mes',
                     ]
                 ]);
    }

    public function test_can_delete_factura(): void
    {
        $factura = Factura::factory()->create([
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->deleteJson("/api/facturas/{$factura->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('facturas', [
            'id' => $factura->id,
        ]);
    }

    public function test_consecutivo_increments_correctly(): void
    {
        $numeroInicial = $this->consecutivo->numero_actual;

        $facturaData = [
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

        $this->withHeader('Authorization', "Bearer {$this->token}")
             ->postJson('/api/facturas', $facturaData);

        $this->consecutivo->refresh();

        $this->assertEquals($numeroInicial + 1, $this->consecutivo->numero_actual);
    }
}
