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
     * @return BelongsTo
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * @return BelongsTo
     */
    public function consecutivo(): BelongsTo
    {
        return $this->belongsTo(Consecutivo::class);
    }

    /**
     * @return HasMany
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class);
    }

    /**
     * @return void
     */
    public function calcularTotal(): void
    {
        $this->total = $this->detalles()->sum('subtotal');
        $this->save();
    }
}
