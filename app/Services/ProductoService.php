<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Producto;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductoService
{
    /**
     * Obtener todos los productos con paginación
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Producto::orderBy('nombre', 'asc')
            ->paginate($perPage);
    }

    /**
     * Buscar producto por ID
     */
    public function findById(int $id): ?Producto
    {
        return Producto::find($id);
    }

    /**
     * Buscar producto por código
     */
    public function findByCodigo(string $codigo): ?Producto
    {
        return Producto::where('codigo', $codigo)->first();
    }

    /**
     * Crear un nuevo producto
     */
    public function create(array $data): Producto
    {
        try {
            DB::beginTransaction();
            
            $producto = Producto::create([
                'nombre' => $data['nombre'],
                'codigo' => $data['codigo'],
                'precio_unitario' => $data['precio_unitario'],
                'descripcion' => $data['descripcion'] ?? null,
            ]);
            
            DB::commit();
            
            Log::info('Producto creado', ['producto_id' => $producto->id]);
            
            return $producto;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear producto', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Actualizar un producto existente
     */
    public function update(Producto $producto, array $data): Producto
    {
        try {
            DB::beginTransaction();
            
            $producto->update(array_filter($data, fn($value) => $value !== null));
            
            DB::commit();
            
            Log::info('Producto actualizado', ['producto_id' => $producto->id]);
            
            return $producto->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar producto', [
                'producto_id' => $producto->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Eliminar un producto
     */
    public function delete(Producto $producto): bool
    {
        try {
            DB::beginTransaction();
            
            // Verificar si tiene facturas asociadas
            if ($producto->facturaDetalles()->count() > 0) {
                throw new \Exception('No se puede eliminar el producto porque está en facturas');
            }
            
            $producto->delete();
            
            DB::commit();
            
            Log::info('Producto eliminado', ['producto_id' => $producto->id]);
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar producto', [
                'producto_id' => $producto->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Buscar productos por término
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return Producto::where(function ($query) use ($term) {
            $query->where('nombre', 'like', "%{$term}%")
                  ->orWhere('codigo', 'like', "%{$term}%")
                  ->orWhere('descripcion', 'like', "%{$term}%");
        })
        ->orderBy('nombre', 'asc')
        ->paginate($perPage);
    }

    /**
     * Obtener productos con stock bajo (placeholder para futura implementación)
     */
    public function getLowStock(int $threshold = 10): array
    {
        // Placeholder para futura funcionalidad de inventario
        return [];
    }
}
