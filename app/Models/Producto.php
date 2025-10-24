<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Producto extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nombre',
        'codigo',
        'precio_unitario',
        'descripcion',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    /**
     * Relación con factura_detalles
     */
    public function facturaDetalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class);
    }
}
