<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_factura' => $this->numero_factura,
            'fecha_emision' => $this->fecha_emision?->format('Y-m-d'),
            'total' => (float) $this->total,
            'cliente_id' => $this->cliente_id,
            'cliente' => new ClienteResource($this->whenLoaded('cliente')),
            'consecutivo_id' => $this->consecutivo_id,
            'detalles' => FacturaDetalleResource::collection($this->whenLoaded('detalles')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
