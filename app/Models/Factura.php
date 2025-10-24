<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Factura extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'cliente_id',
        'consecutivo_id',
        'numero_factura',
        'fecha_emision',
        'total',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'total' => 'decimal:2',
    ];

    /**
     * Relación con cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relación con consecutivo
     */
    public function consecutivo(): BelongsTo
    {
        return $this->belongsTo(Consecutivo::class);
    }

    /**
     * Relación con factura_detalles
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class);
    }

    /**
     * Calcular el total de la factura
     */
    public function calcularTotal(): void
    {
        $this->total = $this->detalles()->sum('subtotal');
        $this->save();
    }
}
