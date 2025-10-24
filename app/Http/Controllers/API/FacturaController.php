<?php
declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Factura\StoreFacturaRequest;
use App\Http\Resources\FacturaResource;
use App\Models\Factura;
use App\Services\FacturaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FacturaController extends Controller
{
    public function __construct(
        private readonly FacturaService $facturaService
    ) {}

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 15);
        $clienteId = $request->input('cliente_id');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        if ($clienteId) {
            $facturas = $this->facturaService->getByCliente((int) $clienteId, $perPage);
        } elseif ($fechaInicio && $fechaFin) {
            $facturas = $this->facturaService->getByDateRange($fechaInicio, $fechaFin, $perPage);
        } else {
            $facturas = $this->facturaService->getAll($perPage);
        }

        return FacturaResource::collection($facturas);
    }

    /**
     * guardar una nueva factura
     * @param StoreFacturaRequest $request
     * @return JsonResponse
     */
    public function store(StoreFacturaRequest $request): JsonResponse
    {
        try {
            $factura = $this->facturaService->create($request->validated());

            return response()->json([
                'message' => 'Factura creada exitosamente',
                'data' => new FacturaResource($factura),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la factura',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $factura = $this->facturaService->findById($id);

        if (!$factura) {
            return response()->json([
                'message' => 'Factura no encontrada',
            ], 404);
        }

        return response()->json([
            'data' => new FacturaResource($factura),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $factura = Factura::find($id);

            if (!$factura) {
                return response()->json([
                    'message' => 'Factura no encontrada',
                ], 404);
            }

            $facturaActualizada = $this->facturaService->update($factura, $request->all());

            return response()->json([
                'message' => 'Factura actualizada exitosamente',
                'data' => new FacturaResource($facturaActualizada),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la factura',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $factura = Factura::find($id);

            if (!$factura) {
                return response()->json([
                    'message' => 'Factura no encontrada',
                ], 404);
            }

            $this->facturaService->delete($factura);

            return response()->json([
                'message' => 'Factura eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la factura',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     *
     * @return JsonResponse
     */
    public function estadisticas(): JsonResponse
    {
        try {
            $estadisticas = $this->facturaService->getEstadisticas();

            return response()->json([
                'data' => $estadisticas,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @param string $numero
     * @return JsonResponse
     */
    public function buscarPorNumero(string $numero): JsonResponse
    {
        $factura = $this->facturaService->findByNumero($numero);

        if (!$factura) {
            return response()->json([
                'message' => 'Factura no encontrada',
            ], 404);
        }

        return response()->json([
            'data' => new FacturaResource($factura),
        ]);
    }
}
