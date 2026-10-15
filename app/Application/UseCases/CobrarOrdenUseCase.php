<?php

namespace App\Application\UseCases;

use App\Application\Services\AsientoService;
use App\Application\Services\MovimientoService;
use App\Domain\Pagos\FabricaMedios;
use App\Models\Payment;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class CobrarOrdenUseCase
{
    public function __construct(private MovimientoService $dinero, private AsientoService $contable) {}

    public function ejecutar(int $ordenId, string $medioNombre, ?int $bancoId, float $ingresado): Payment
    {
        $medio = FabricaMedios::crear($medioNombre);
        return DB::transaction(function () use ($ordenId, $medio, $bancoId, $ingresado) {
            $ot = WorkOrder::findOrFail($ordenId);
            $total = (float) $ot->total;
            if ($medio->nombre() !== 'credito' && $ingresado < $total) {
                throw new \DomainException('El monto no cubre el total. Ej: recibe '.number_format($total, 2).' o elige crédito.');
            }
            $cuenta = $medio->cuentaDestino($bancoId);
            if ($medio->nombre() === 'transferencia' && ! $cuenta) {
                throw new \DomainException('Elige el banco destino. Ej: Banco Unión.');
            }
            $pago = $ot->payments()->create([
                'medio' => $medio->nombre(), 'bank_account_id' => $cuenta,
                'total' => $total, 'recibido' => $medio->recibido($total, $ingresado),
                'vuelto' => $medio->vuelto($total, $ingresado), 'saldo_pendiente' => $medio->deuda($total, $ingresado),
                'user_id' => auth()->id(),
            ]);
            $esCredito = $medio->nombre() === 'credito';
            $ot->update(['estado' => $esCredito ? 'cerrada-pendiente' : 'cerrada-pagada', 'cerrada_at' => now()]);
            $ot->logs()->create(['user_id' => auth()->id(), 'accion' => 'cobrada', 'detalle' => ['medio' => $medio->nombre(), 'total' => $total]]);
            if (! $esCredito) {
                $this->dinero->registrar(['tipo' => 'entrada', 'cuenta_id' => $cuenta, 'monto' => $ingresado, 'motivo' => "Cobro orden {$ot->numero}", 'origen_tipo' => 'cobro', 'origen_id' => $pago->id]);
            }
            $this->contable->ventaServicio((float) $ot->total_repuestos, (float) $ot->mano_obra, $medio->nombre(), $cuenta, $ot->numero);
            return $pago;
        });
    }
}
