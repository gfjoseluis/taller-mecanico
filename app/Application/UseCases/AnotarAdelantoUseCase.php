<?php

namespace App\Application\UseCases;

use App\Application\Services\AsientoService;
use App\Application\Services\MovimientoService;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

/** Adelanto en orden abierta: entra dinero, la orden sigue abierta. */
class AnotarAdelantoUseCase
{
    public function __construct(private MovimientoService $dinero, private AsientoService $contable) {}

    public function adelantado(WorkOrder $ot): float
    {
        return (float) $ot->payments()->sum('recibido');
    }

    public function falta(WorkOrder $ot): float
    {
        return max(0, (float) $ot->total - $this->adelantado($ot));
    }

    public function ejecutar(int $ordenId, string $medio, ?int $bancoId, float $monto): void
    {
        DB::transaction(function () use ($ordenId, $medio, $bancoId, $monto) {
            $ot = WorkOrder::with('payments')->findOrFail($ordenId);
            if ($ot->estado !== 'registrada') throw new \DomainException('Solo órdenes abiertas reciben adelanto.');
            $falta = $this->falta($ot);
            if ($monto <= 0 || $monto > $falta) throw new \DomainException('Monto inválido. Ej: hasta '.number_format($falta, 2).'.');
            $ot->payments()->create([
                'medio' => $medio, 'bank_account_id' => $medio === 'transferencia' ? $bancoId : null,
                'total' => (float) $ot->total, 'recibido' => $monto, 'vuelto' => 0, 'saldo_pendiente' => 0,
                'user_id' => auth()->id(),
            ]);
            $ot->logs()->create(['user_id' => auth()->id(), 'accion' => 'adelanto', 'detalle' => ['monto' => $monto, 'medio' => $medio]]);
            $cuenta = $medio === 'transferencia' ? $bancoId : $this->dinero->caja()->id;
            $this->dinero->registrar(['tipo' => 'entrada', 'cuenta_id' => $cuenta, 'monto' => $monto, 'motivo' => "Adelanto orden {$ot->numero}", 'origen_tipo' => 'adelanto-cliente', 'origen_id' => $ot->id]);
            $this->contable->adelantoCliente($monto, $cuenta, $ot->numero);
        });
    }
}
