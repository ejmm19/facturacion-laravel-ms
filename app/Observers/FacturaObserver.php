<?php
declare(strict_types=1);

namespace App\Observers;

use App\Models\Factura;
use App\Jobs\EnviarFacturaExternaJob;
use Illuminate\Support\Facades\Log;

class FacturaObserver
{
    /**
     * Handle the Factura "created" event.
     */
    public function created(Factura $factura): void
    {
        if (app()->environment('testing') && !config('services.factura_externa.enabled_in_tests', false)) {
            return;
        }

        EnviarFacturaExternaJob::dispatch($factura)
            ->onQueue('facturas')
            ->delay(now()->addSeconds(5));

        Log::info('Job de envío de factura despachado a la cola', [
            'factura_id' => $factura->id,
            'numero_factura' => $factura->numero_factura,
        ]);
    }

    /**
     * Handle the Factura "updated" event.
     */
    public function updated(Factura $factura): void
    {

    }

    /**
     * Handle the Factura "deleted" event.
     */
    public function deleted(Factura $factura): void
    {
        //
    }

    /**
     * Handle the Factura "restored" event.
     */
    public function restored(Factura $factura): void
    {
        //
    }

    /**
     * Handle the Factura "force deleted" event.
     */
    public function forceDeleted(Factura $factura): void
    {
        //
    }
}
