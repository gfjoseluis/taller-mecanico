<?php

namespace App\Application\UseCases;

use App\Domain\Contracts\FacturacionGatewayInterface;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class EmitirFacturaUseCase
{
    public function __construct(private FacturacionGatewayInterface $gateway) {}

    public function ejecutar(string $origenTipo, int $origenId, int $clientId, float $total): Invoice
    {
        return DB::transaction(function () use ($origenTipo, $origenId, $clientId, $total) {
            $invoice = Invoice::create([
                'origen_tipo' => $origenTipo, 'origen_id' => $origenId,
                'client_id' => $clientId, 'total' => $total, 'estado' => 'pendiente',
            ]);
            try {
                $res = $this->gateway->emitir([
                    'origen_tipo' => $origenTipo, 'origen_id' => $origenId,
                    'client_id' => $clientId, 'total' => $total,
                    'sucursal' => config('siat.sucursal'), 'ambiente' => config('siat.ambiente'),
                ]);
                $invoice->update(['cuf' => $res['cuf'], 'numero' => $res['numero'], 'qr' => $res['qr'], 'estado' => 'validada', 'raw' => $res['raw']]);
            } catch (\Throwable $e) {
                $invoice->update(['estado' => 'error', 'raw' => ['error' => $e->getMessage()]]);
                throw $e;
            }
            return $invoice;
        });
    }
}
