<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Consecutivo extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'prefijo',
        'numero_actual',
    ];

    protected $casts = [
        'numero_actual' => 'integer',
    ];

    /**
     * Relación con facturas
     */
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }

    /**
     * Obtener el siguiente número de factura
     */
    public function obtenerSiguienteNumero(): string
    {
        $this->increment('numero_actual');
        $this->refresh();
        
        return $this->prefijo . str_pad((string)$this->numero_actual, 6, '0', STR_PAD_LEFT);
    }
}
