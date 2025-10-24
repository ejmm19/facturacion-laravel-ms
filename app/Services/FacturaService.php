<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\Consecutivo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacturaService
{
    /**
     * obtener todas
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Factura::with(['cliente', 'consecutivo'])
            ->orderBy('fecha_emision', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Buscar factura por ID
     * @param int $id
     * @return Factura|null
     */
    public function findById(int $id): ?Factura
    {
        return Factura::with(['cliente', 'consecutivo', 'detalles.producto'])
            ->find($id);
    }

    /**
     * buscar por numero de factura
     * @param string $numero
     * @return Factura|null
     */
    public function findByNumero(string $numero): ?Factura
    {
        return Factura::with(['cliente', 'consecutivo', 'detalles.producto'])
            ->where('numero_factura', $numero)
            ->first();
    }

    /**
     * crear una nueva factura
     * @param array $data
     * @return Factura
     * @throws \Exception
     */
    public function create(array $data): Factura
    {
        try {
            DB::beginTransaction();

            // Obtener consecutivo y generar número de factura
            $consecutivo = Consecutivo::findOrFail($data['consecutivo_id']);
            $numeroFactura = $consecutivo->obtenerSiguienteNumero();

            // Crear factura
            $factura = Factura::create([
                'cliente_id' => $data['cliente_id'],
                'consecutivo_id' => $data['consecutivo_id'],
                'numero_factura' => $numeroFactura,
                'fecha_emision' => $data['fecha_emision'],
                'total' => 0,
            ]);

            // Crear detalles
            $total = 0;
            foreach ($data['detalles'] as $detalle) {
                $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];

                FacturaDetalle::create([
                    'factura_id' => $factura->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            // Actualizar total de la factura
            $factura->update(['total' => $total]);

            DB::commit();

            Log::info('Factura creada', [
                'factura_id' => $factura->id,
                'numero_factura' => $factura->numero_factura
            ]);

            return $factura->fresh(['cliente', 'consecutivo', 'detalles.producto']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear factura', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Actualizar una factura existente
     * @param Factura $factura
     * @param array $data
     * @return Factura
     * @throws \Exception
     */
    public function update(Factura $factura, array $data): Factura
    {
        try {
            DB::beginTransaction();

            // Actualizar datos básicos
            if (isset($data['cliente_id'])) {
                $factura->cliente_id = $data['cliente_id'];
            }
            if (isset($data['fecha_emision'])) {
                $factura->fecha_emision = $data['fecha_emision'];
            }

            // Si hay detalles, actualizar
            if (isset($data['detalles'])) {
                // Eliminar detalles anteriores
                $factura->detalles()->delete();

                // Crear nuevos detalles
                $total = 0;
                foreach ($data['detalles'] as $detalle) {
                    $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];

                    FacturaDetalle::create([
                        'factura_id' => $factura->id,
                        'producto_id' => $detalle['producto_id'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => $subtotal,
                    ]);

                    $total += $subtotal;
                }

                $factura->total = $total;
            }

            $factura->save();

            DB::commit();

            Log::info('Factura actualizada', ['factura_id' => $factura->id]);

            return $factura->fresh(['cliente', 'consecutivo', 'detalles.producto']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar factura', [
                'factura_id' => $factura->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Eliminar una factura
     * @param Factura $factura
     * @return bool
     * @throws \Exception
     */
    public function delete(Factura $factura): bool
    {
        try {
            DB::beginTransaction();

            $factura->delete();

            DB::commit();

            Log::info('Factura eliminada', ['factura_id' => $factura->id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar factura', [
                'factura_id' => $factura->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Buscar facturas por cliente
     * @param int $clienteId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByCliente(int $clienteId, int $perPage = 15): LengthAwarePaginator
    {
        return Factura::with(['consecutivo', 'detalles.producto'])
            ->where('cliente_id', $clienteId)
            ->orderBy('fecha_emision', 'desc')
            ->paginate($perPage);
    }

    /**
     * Buscar facturas por rango de fechas
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByDateRange(string $fechaInicio, string $fechaFin, int $perPage = 15): LengthAwarePaginator
    {
        return Factura::with(['cliente', 'consecutivo'])
            ->whereBetween('fecha_emision', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_emision', 'desc')
            ->paginate($perPage);
    }

    /**
     *
     * @return array
     */
    public function getEstadisticas(): array
    {
        return [
            'total_facturas' => Factura::count(),
            'total_ventas' => Factura::sum('total'),
            'promedio_venta' => Factura::avg('total'),
            'facturas_hoy' => Factura::whereDate('fecha_emision', today())->count(),
            'ventas_hoy' => Factura::whereDate('fecha_emision', today())->sum('total'),
            'facturas_mes' => Factura::whereMonth('fecha_emision', now()->month)
                                     ->whereYear('fecha_emision', now()->year)
                                     ->count(),
            'ventas_mes' => Factura::whereMonth('fecha_emision', now()->month)
                                   ->whereYear('fecha_emision', now()->year)
                                   ->sum('total'),
        ];
    }
}
