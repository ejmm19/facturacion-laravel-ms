<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\Factura;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnviarFacturaExternaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de intentos del job
     */
    public int $tries = 3;

    /**
     * Tiempo de espera entre reintentos en segundos
     */
    public int $backoff = 60;

    /**
     * Tiempo máximo de ejecución en segundos
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Factura $factura
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Cargar las relaciones necesarias
            $this->factura->load(['cliente', 'detalles.producto']);

            // Preparar la estructura de datos
            $payload = [
                'data' => [
                    'id' => $this->factura->id,
                    'numero_factura' => $this->factura->numero_factura,
                    'fecha_emision' => $this->factura->fecha_emision->format('Y-m-d'),
                    'total' => (float) $this->factura->total,
                    'cliente_id' => $this->factura->cliente_id,
                    'cliente' => [
                        'id' => $this->factura->cliente->id,
                        'nombre' => $this->factura->cliente->nombre,
                        'email' => $this->factura->cliente->email,
                        'telefono' => $this->factura->cliente->telefono,
                        'direccion' => $this->factura->cliente->direccion,
                        'identificacion' => $this->factura->cliente->identificacion,
                        'created_at' => $this->factura->cliente->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $this->factura->cliente->updated_at->format('Y-m-d H:i:s'),
                    ],
                    'consecutivo_id' => $this->factura->consecutivo_id,
                    'detalles' => $this->factura->detalles->map(function ($detalle) {
                        return [
                            'id' => $detalle->id,
                            'producto_id' => $detalle->producto_id,
                            'producto' => [
                                'id' => $detalle->producto->id,
                                'nombre' => $detalle->producto->nombre,
                                'codigo' => $detalle->producto->codigo,
                                'precio_unitario' => (float) $detalle->producto->precio_unitario,
                                'descripcion' => $detalle->producto->descripcion,
                                'created_at' => $detalle->producto->created_at->format('Y-m-d H:i:s'),
                                'updated_at' => $detalle->producto->updated_at->format('Y-m-d H:i:s'),
                            ],
                            'cantidad' => $detalle->cantidad,
                            'precio_unitario' => (float) $detalle->precio_unitario,
                            'subtotal' => (float) $detalle->subtotal,
                        ];
                    })->toArray(),
                    'created_at' => $this->factura->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $this->factura->updated_at->format('Y-m-d H:i:s'),
                ]
            ];

            // Obtener URL del endpoint externo
            $url = config('services.factura_externa.url', 'http://localhost:3000/facturas/recibir');

            // Enviar la petición HTTP
            $response = Http::timeout(30)
                ->retry(3, 100)
                ->post($url, $payload);

            // Verificar respuesta
            if ($response->successful()) {
                Log::info('Factura enviada exitosamente al sistema externo', [
                    'factura_id' => $this->factura->id,
                    'numero_factura' => $this->factura->numero_factura,
                    'status_code' => $response->status(),
                ]);
            } else {
                Log::warning('Error al enviar factura al sistema externo', [
                    'factura_id' => $this->factura->id,
                    'numero_factura' => $this->factura->numero_factura,
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                ]);

                // Lanzar excepción para que se reintente
                throw new \Exception("Error al enviar factura: Status {$response->status()}");
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('RequestException al enviar factura al sistema externo', [
                'factura_id' => $this->factura->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw new \Exception("Error al enviar factura: {$e->getMessage()}");
        } catch (\Exception $e) {
            Log::error('Excepción al enviar factura al sistema externo', [
                'factura_id' => $this->factura->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Re-lanzar la excepción para que Laravel maneje los reintentos
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('Job de envío de factura falló después de todos los intentos', [
            'factura_id' => $this->factura->id,
            'numero_factura' => $this->factura->numero_factura,
            'error' => $exception->getMessage(),
        ]);
    }
}
