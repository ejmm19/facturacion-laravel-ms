<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_list_productos(): void
    {
        Producto::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson('/api/productos');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'nombre', 'codigo', 'precio_unitario']
                     ]
                 ]);
    }

    public function test_can_create_producto(): void
    {
        $productoData = [
            'nombre' => 'Laptop HP',
            'codigo' => 'LAP001',
            'precio_unitario' => 1500.00,
            'descripcion' => 'Laptop de alta gama',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->postJson('/api/productos', $productoData);

        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Producto creado exitosamente',
                 ]);

        $this->assertDatabaseHas('productos', [
            'codigo' => 'LAP001',
            'nombre' => 'Laptop HP',
        ]);
    }

    public function test_cannot_create_producto_with_duplicate_codigo(): void
    {
        Producto::factory()->create(['codigo' => 'PROD001']);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->postJson('/api/productos', [
                             'nombre' => 'Test',
                             'codigo' => 'PROD001',
                             'precio_unitario' => 100.00,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['codigo']);
    }

    public function test_cannot_create_producto_with_negative_price(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->postJson('/api/productos', [
                             'nombre' => 'Test',
                             'codigo' => 'PROD001',
                             'precio_unitario' => -100.00,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['precio_unitario']);
    }

    public function test_can_show_producto(): void
    {
        $producto = Producto::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson("/api/productos/{$producto->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'id' => $producto->id,
                         'nombre' => $producto->nombre,
                         'codigo' => $producto->codigo,
                     ]
                 ]);
    }

    public function test_can_update_producto(): void
    {
        $producto = Producto::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->putJson("/api/productos/{$producto->id}", [
                             'nombre' => 'Producto Actualizado',
                             'precio_unitario' => 2000.00,
                         ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'nombre' => 'Producto Actualizado',
        ]);
    }

    public function test_can_delete_producto(): void
    {
        $producto = Producto::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->deleteJson("/api/productos/{$producto->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('productos', [
            'id' => $producto->id,
        ]);
    }

    public function test_can_search_productos(): void
    {
        Producto::factory()->create(['nombre' => 'Laptop Dell']);
        Producto::factory()->create(['nombre' => 'Mouse Logitech']);
        Producto::factory()->create(['nombre' => 'Teclado Mecánico']);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson('/api/productos?search=Laptop');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }
}
