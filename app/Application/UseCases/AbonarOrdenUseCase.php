<?php

namespace App\Application\UseCases;

use App\Application\Services\AsientoService;
use App\Application\Services\MovimientoService;
use App\Models\Product;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class AbonarOrdenUseCase
{
    public function __construct(private MovimientoService $dinero, private AsientoService $contable) {}

    public function pendiente(WorkOrder $ot): float
    {
        return max(0, (float) $ot->total - (float) $ot->payments()->sum('recibido'));
    }

    public function ejecutar(int $ordenId, string $medio, ?int $bancoId, float $monto): void
    {
        DB::transaction(function () use ($ordenId, $medio, $bancoId, $monto) {
            $ot = WorkOrder::with('payments')->findOrFail($ordenId);
            if ($ot->estado !== 'cerrada-pendiente') throw new \DomainException('Solo órdenes con deuda.');
            if ($monto <= 0 || $monto > $this->pendiente($ot)) throw new \DomainException('Monto inválido. Ej: hasta '.number_format($this->pendiente($ot), 2).'.');
            $ot->payments()->create([
                'medio' => $medio, 'bank_account_id' => $medio === 'transferencia' ? $bancoId : null,
                'total' => (float) $ot->total, 'recibido' => $monto, 'vuelto' => 0, 'saldo_pendiente' => 0,
                'user_id' => auth()->id(),
            ]);
            if ($monto >= $this->pendiente($ot)) $ot->update(['estado' => 'cerrada-pagada']);
            $ot->logs()->create(['user_id' => auth()->id(), 'accion' => 'abono', 'detalle' => ['monto' => $monto, 'medio' => $medio]]);
            $cuenta = $medio === 'transferencia' ? $bancoId : $this->dinero->caja()->id;
            $this->dinero->registrar(['tipo' => 'entrada', 'cuenta_id' => $cuenta, 'monto' => $monto, 'motivo' => "Abono orden {$ot->numero}", 'origen_tipo' => 'abono-cliente', 'origen_id' => $ot->id]);
            $this->contable->abonoCliente($monto, $cuenta, $ot->numero);
        });
    }
}
