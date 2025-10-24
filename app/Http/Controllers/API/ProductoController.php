<?php
declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Producto\StoreProductoRequest;
use App\Http\Requests\Producto\UpdateProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use App\Services\ProductoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductoController extends Controller
{
    public function __construct(
        private readonly ProductoService $productoService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');

        $productos = $search
            ? $this->productoService->search($search, $perPage)
            : $this->productoService->getAll($perPage);

        return ProductoResource::collection($productos);
    }

    /**
     * guardar un nuevo producto
     * @param StoreProductoRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductoRequest $request): JsonResponse
    {
        try {
            $producto = $this->productoService->create($request->validated());

            return response()->json([
                'message' => 'Producto creado exitosamente',
                'data' => new ProductoResource($producto),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el producto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar un producto por su ID
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $producto = $this->productoService->findById($id);

        if (!$producto) {
            return response()->json([
                'message' => 'Producto no encontrado',
            ], 404);
        }

        return response()->json([
            'data' => new ProductoResource($producto),
        ]);
    }

    /**
     * Actualizar un producto existente
     * @param UpdateProductoRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateProductoRequest $request, int $id): JsonResponse
    {
        try {
            $producto = Producto::find($id);

            if (!$producto) {
                return response()->json([
                    'message' => 'Producto no encontrado',
                ], 404);
            }

            $productoActualizado = $this->productoService->update($producto, $request->validated());

            return response()->json([
                'message' => 'Producto actualizado exitosamente',
                'data' => new ProductoResource($productoActualizado),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el producto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar un producto por su ID
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $producto = Producto::find($id);

            if (!$producto) {
                return response()->json([
                    'message' => 'Producto no encontrado',
                ], 404);
            }

            $this->productoService->delete($producto);

            return response()->json([
                'message' => 'Producto eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el producto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
