<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClienteService
{
    /**
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Cliente::withCount('facturas')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * @param int $id
     * @return Cliente|null
     */
    public function findById(int $id): ?Cliente
    {
        return Cliente::with(['facturas' => function ($query) {
            $query->orderBy('fecha_emision', 'desc')->limit(10);
        }])
        ->withCount('facturas')
        ->find($id);
    }

    /**
     * @param array $data
     * @return Cliente
     * @throws \Exception
     */
    public function create(array $data): Cliente
    {
        try {
            DB::beginTransaction();

            $cliente = Cliente::create([
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'telefono' => $data['telefono'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'identificacion' => $data['identificacion'],
            ]);

            DB::commit();

            Log::info('Cliente creado', ['cliente_id' => $cliente->id]);

            return $cliente;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cliente', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * @param Cliente $cliente
     * @param array $data
     * @return Cliente
     * @throws \Exception
     */
    public function update(Cliente $cliente, array $data): Cliente
    {
        try {
            DB::beginTransaction();

            $cliente->update(array_filter($data, fn($value) => $value !== null));

            DB::commit();

            Log::info('Cliente actualizado', ['cliente_id' => $cliente->id]);

            return $cliente->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cliente', [
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * @param Cliente $cliente
     * @return bool
     * @throws \Exception
     */
    public function delete(Cliente $cliente): bool
    {
        try {
            DB::beginTransaction();

            // Verificar si tiene facturas
            if ($cliente->facturas()->count() > 0) {
                throw new \Exception('No se puede eliminar el cliente porque tiene facturas asociadas');
            }

            $cliente->delete();

            DB::commit();

            Log::info('Cliente eliminado', ['cliente_id' => $cliente->id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar cliente', [
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * @param string $term
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return Cliente::where(function ($query) use ($term) {
            $query->where('nombre', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('identificacion', 'like', "%{$term}%");
        })
        ->withCount('facturas')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
    }
}
