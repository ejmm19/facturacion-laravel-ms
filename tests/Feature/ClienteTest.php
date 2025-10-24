<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
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

    public function test_can_list_clientes(): void
    {
        Cliente::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson('/api/clientes');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'nombre', 'email', 'identificacion']
                     ]
                 ]);
    }

    public function test_can_create_cliente(): void
    {
        $clienteData = [
            'nombre' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'telefono' => '555-0101',
            'direccion' => 'Calle 123',
            'identificacion' => '1234567890',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->postJson('/api/clientes', $clienteData);

        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Cliente creado exitosamente',
                 ])
                 ->assertJsonStructure([
                     'data' => ['id', 'nombre', 'email', 'identificacion']
                 ]);

        $this->assertDatabaseHas('clientes', [
            'email' => 'juan@example.com',
            'identificacion' => '1234567890',
        ]);
    }

    public function test_cannot_create_cliente_with_duplicate_email(): void
    {
        Cliente::factory()->create(['email' => 'test@example.com']);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->postJson('/api/clientes', [
                             'nombre' => 'Test',
                             'email' => 'test@example.com',
                             'identificacion' => '9999999999',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_create_cliente_with_duplicate_identificacion(): void
    {
        Cliente::factory()->create(['identificacion' => '1234567890']);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->postJson('/api/clientes', [
                             'nombre' => 'Test',
                             'email' => 'new@example.com',
                             'identificacion' => '1234567890',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['identificacion']);
    }

    public function test_can_show_cliente(): void
    {
        $cliente = Cliente::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson("/api/clientes/{$cliente->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'id' => $cliente->id,
                         'nombre' => $cliente->nombre,
                         'email' => $cliente->email,
                     ]
                 ]);
    }

    public function test_returns_404_for_nonexistent_cliente(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson('/api/clientes/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_cliente(): void
    {
        $cliente = Cliente::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->putJson("/api/clientes/{$cliente->id}", [
                             'nombre' => 'Nombre Actualizado',
                             'telefono' => '555-9999',
                         ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Cliente actualizado exitosamente',
                 ]);

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'nombre' => 'Nombre Actualizado',
            'telefono' => '555-9999',
        ]);
    }

    public function test_can_delete_cliente_without_facturas(): void
    {
        $cliente = Cliente::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->deleteJson("/api/clientes/{$cliente->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Cliente eliminado exitosamente',
                 ]);

        $this->assertDatabaseMissing('clientes', [
            'id' => $cliente->id,
        ]);
    }

    public function test_can_search_clientes(): void
    {
        Cliente::factory()->create(['nombre' => 'Juan Pérez']);
        Cliente::factory()->create(['nombre' => 'María García']);
        Cliente::factory()->create(['nombre' => 'Pedro López']);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson('/api/clientes?search=Juan');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    public function test_requires_authentication_to_access_clientes(): void
    {
        $response = $this->getJson('/api/clientes');

        $response->assertStatus(401);
    }
}
