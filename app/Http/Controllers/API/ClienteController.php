<?php
declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\StoreClienteRequest;
use App\Http\Requests\Cliente\UpdateClienteRequest;
use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use App\Services\ClienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClienteController extends Controller
{
    public function __construct(
        private readonly ClienteService $clienteService
    ) {}

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');

        $clientes = $search
            ? $this->clienteService->search($search, $perPage)
            : $this->clienteService->getAll($perPage);

        return ClienteResource::collection($clientes);
    }

    /**
     * @param StoreClienteRequest $request
     * @return JsonResponse
     */
    public function store(StoreClienteRequest $request): JsonResponse
    {
        try {
            $cliente = $this->clienteService->create($request->validated());

            return response()->json([
                'message' => 'Cliente creado exitosamente',
                'data' => new ClienteResource($cliente),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el cliente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $cliente = $this->clienteService->findById($id);

        if (!$cliente) {
            return response()->json([
                'message' => 'Cliente no encontrado',
            ], 404);
        }

        return response()->json([
            'data' => new ClienteResource($cliente),
        ]);
    }

    /**
     * @param UpdateClienteRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateClienteRequest $request, int $id): JsonResponse
    {
        try {
            $cliente = Cliente::find($id);

            if (!$cliente) {
                return response()->json([
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            $clienteActualizado = $this->clienteService->update($cliente, $request->validated());

            return response()->json([
                'message' => 'Cliente actualizado exitosamente',
                'data' => new ClienteResource($clienteActualizado),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el cliente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $cliente = Cliente::find($id);

            if (!$cliente) {
                return response()->json([
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            $this->clienteService->delete($cliente);

            return response()->json([
                'message' => 'Cliente eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el cliente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
