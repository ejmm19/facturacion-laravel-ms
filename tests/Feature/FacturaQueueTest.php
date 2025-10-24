<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Consecutivo;
use App\Models\Factura;
use App\Jobs\EnviarFacturaExternaJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FacturaQueueTest extends TestCase
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

    public function test_job_is_dispatched_when_factura_is_created(): void
    {
        // Habilitar el envío de facturas en testing
        config(['services.factura_externa.enabled_in_tests' => true]);
        
        Queue::fake();

        $facturaData = [
            'cliente_id' => $this->cliente->id,
            'consecutivo_id' => $this->consecutivo->id,
            'fecha_emision' => now()->format('Y-m-d'),
            'detalles' => [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 1,
                    'precio_unitario' => $this->producto->precio_unitario,
                ]
            ]
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->postJson('/api/facturas', $facturaData);

        $response->assertStatus(201);

        // Verificar que el job fue despachado
        Queue::assertPushed(EnviarFacturaExternaJob::class, function ($job) {
            return $job->factura instanceof Factura;
        });
    }

    public function test_job_sends_correct_data_structure(): void
    {
        Http::fake([
            '*' => Http::response(['success' => true], 200),
        ]);

        $factura = Factura::factory()
            ->for($this->cliente)
            ->for($this->consecutivo)
            ->create(['total' => 1500.00]);

        $factura->detalles()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 1,
            'precio_unitario' => 1500.00,
            'subtotal' => 1500.00,
        ]);

        $factura->load(['cliente', 'detalles.producto']);

        // Ejecutar el job
        $job = new EnviarFacturaExternaJob($factura);
        $job->handle();

        // Verificar que se hizo el request HTTP
        Http::assertSent(function ($request) use ($factura) {
            $data = $request->data();
            
            // Verificar estructura sin importar la URL exacta
            return isset($data['data']) &&
                   $data['data']['id'] === $factura->id &&
                   $data['data']['numero_factura'] === $factura->numero_factura &&
                   isset($data['data']['cliente']) &&
                   isset($data['data']['detalles']) &&
                   is_array($data['data']['detalles']);
        });
    }

    public function test_job_includes_all_required_fields(): void
    {
        Http::fake([
            '*' => Http::response(['success' => true], 200),
        ]);

        $factura = Factura::factory()
            ->for($this->cliente)
            ->for($this->consecutivo)
            ->create();

        $factura->detalles()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 2,
            'precio_unitario' => 100.00,
            'subtotal' => 200.00,
        ]);

        $factura->load(['cliente', 'detalles.producto']);

        $job = new EnviarFacturaExternaJob($factura);
        $job->handle();

        Http::assertSent(function ($request) {
            $data = $request->data()['data'] ?? [];
            
            // Verificar estructura principal
            $hasMainFields = isset($data['id']) &&
                           isset($data['numero_factura']) &&
                           isset($data['fecha_emision']) &&
                           isset($data['total']) &&
                           isset($data['cliente_id']) &&
                           isset($data['consecutivo_id']);

            // Verificar cliente completo
            $hasCliente = isset($data['cliente']) &&
                        isset($data['cliente']['id']) &&
                        isset($data['cliente']['nombre']) &&
                        isset($data['cliente']['email']) &&
                        isset($data['cliente']['identificacion']);

            // Verificar detalles
            $hasDetalles = isset($data['detalles']) &&
                         is_array($data['detalles']) &&
                         count($data['detalles']) > 0;

            if ($hasDetalles) {
                $detalle = $data['detalles'][0];
                $hasDetalleFields = isset($detalle['id']) &&
                                  isset($detalle['producto_id']) &&
                                  isset($detalle['producto']) &&
                                  isset($detalle['cantidad']) &&
                                  isset($detalle['precio_unitario']) &&
                                  isset($detalle['subtotal']);

                // Verificar producto dentro del detalle
                $hasProducto = isset($detalle['producto']['id']) &&
                             isset($detalle['producto']['nombre']) &&
                             isset($detalle['producto']['codigo']) &&
                             isset($detalle['producto']['precio_unitario']);
                
                return $hasMainFields && $hasCliente && $hasDetalles && 
                       $hasDetalleFields && $hasProducto;
            }

            return $hasMainFields && $hasCliente && $hasDetalles;
        });
    }

    public function test_job_logs_error_on_failure(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'Server error'], 500),
        ]);

        $factura = Factura::factory()
            ->for($this->cliente)
            ->for($this->consecutivo)
            ->create();

        $factura->detalles()->create([
            'producto_id' => $this->producto->id,
            'cantidad' => 1,
            'precio_unitario' => 100.00,
            'subtotal' => 100.00,
        ]);

        $job = new EnviarFacturaExternaJob($factura);

        try {
            $job->handle();
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Verificar que la excepción contiene el mensaje esperado
            $this->assertStringContainsString('Error al enviar factura', $e->getMessage());
            $this->assertStringContainsString('500', $e->getMessage());
        }
    }

    public function test_observer_is_triggered_on_factura_creation(): void
    {
        // Habilitar el envío de facturas en testing
        config(['services.factura_externa.enabled_in_tests' => true]);
        
        Queue::fake();

        // Crear factura directamente (no a través del API)
        $factura = Factura::factory()
            ->for($this->cliente)
            ->for($this->consecutivo)
            ->create();

        // Verificar que el observer despachó el job
        Queue::assertPushed(EnviarFacturaExternaJob::class, 1);
    }
}
