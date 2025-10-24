<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Cliente;
use App\Services\ClienteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClienteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ClienteService::class);
    }

    public function test_can_create_cliente(): void
    {
        $data = [
            'nombre' => 'Test Cliente',
            'email' => 'test@example.com',
            'telefono' => '555-0000',
            'direccion' => 'Test Address',
            'identificacion' => '1234567890',
        ];

        $cliente = $this->service->create($data);

        $this->assertInstanceOf(Cliente::class, $cliente);
        $this->assertEquals('Test Cliente', $cliente->nombre);
        $this->assertEquals('test@example.com', $cliente->email);
    }

    public function test_can_find_cliente_by_id(): void
    {
        $cliente = Cliente::factory()->create();

        $found = $this->service->findById($cliente->id);

        $this->assertNotNull($found);
        $this->assertEquals($cliente->id, $found->id);
    }

    public function test_returns_null_for_nonexistent_cliente(): void
    {
        $found = $this->service->findById(99999);

        $this->assertNull($found);
    }

    public function test_can_update_cliente(): void
    {
        $cliente = Cliente::factory()->create();

        $updated = $this->service->update($cliente, [
            'nombre' => 'Updated Name',
        ]);

        $this->assertEquals('Updated Name', $updated->nombre);
    }

    public function test_can_delete_cliente_without_facturas(): void
    {
        $cliente = Cliente::factory()->create();

        $result = $this->service->delete($cliente);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }

    public function test_can_search_clientes(): void
    {
        Cliente::factory()->create(['nombre' => 'Juan Pérez']);
        Cliente::factory()->create(['nombre' => 'María García']);

        $results = $this->service->search('Juan');

        $this->assertGreaterThan(0, $results->total());
    }

    public function test_get_all_returns_paginated_results(): void
    {
        Cliente::factory()->count(20)->create();

        $results = $this->service->getAll(10);

        $this->assertEquals(10, $results->perPage());
        $this->assertGreaterThan(0, $results->total());
    }
}
