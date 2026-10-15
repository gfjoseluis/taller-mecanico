<?php

namespace App\Application\Services;

use App\Models\BankAccount;
use App\Models\BankMovement;
use Illuminate\Support\Facades\DB;

/**
 * Toda dinero que se mueve pasa por aquí: transacción + bloqueo de fila
 * (dos cajeros a la vez no descuentan mal) + saldo siempre calculado.
 */
class MovimientoService
{
    public function caja(): BankAccount
    {
        return BankAccount::where('banco', 'Efectivo')
            ->orWhere('numero', 'CAJA-001')->orderBy('id')->firstOrFail();
    }

    /** Registra movimiento y actualiza saldos en la misma transacción. */
    public function registrar(array $d): BankMovement
    {
        return DB::transaction(function () use ($d) {
            $cuenta = BankAccount::where('id', $d['cuenta_id'])->lockForUpdate()->firstOrFail();
            $mov = BankMovement::create([
                'fecha' => $d['fecha'] ?? now()->toDateString(),
                'tipo' => $d['tipo'],
                'cuenta_id' => $cuenta->id,
                'cuenta_destino_id' => $d['cuenta_destino_id'] ?? null,
                'partner_id' => $d['partner_id'] ?? null,
                'monto' => $d['monto'],
                'motivo' => $d['motivo'],
                'origen_tipo' => $d['origen_tipo'] ?? 'manual',
                'origen_id' => $d['origen_id'] ?? null,
                'user_id' => $d['user_id'] ?? auth()->id(),
            ]);
            match ($d['tipo']) {
                'entrada', 'aporte' => $cuenta->increment('saldo', $d['monto']),
                'salida', 'retiro' => $cuenta->decrement('saldo', $d['monto']),
                'traspaso' => $this->traspaso($cuenta, $d),
                default => throw new \InvalidArgumentException('Tipo inválido: '.$d['tipo']),
            };
            return $mov;
        });
    }

    private function traspaso(BankAccount $origen, array $d): void
    {
        $destino = BankAccount::where('id', $d['cuenta_destino_id'])->lockForUpdate()->firstOrFail();
        $origen->decrement('saldo', $d['monto']);
        $destino->increment('saldo', $d['monto']);
    }

    /** Reversión: nunca borra el original, crea el inverso (el saldo no miente). */
    public function revertir(BankMovement $mov, string $motivo): BankMovement
    {
        $inverso = match ($mov->tipo) {
            'entrada', 'aporte' => 'salida',
            'salida', 'retiro' => 'entrada',
            default => throw new \InvalidArgumentException('Traspaso se revierte por lados, no global.'),
        };
        return $this->registrar([
            'tipo' => $inverso,
            'cuenta_id' => $mov->cuenta_id,
            'monto' => $mov->monto,
            'motivo' => 'Reversión: '.$motivo,
            'origen_tipo' => 'reversion', 'origen_id' => $mov->id,
        ]);
    }

    /** Saldo calculado desde el corte (auditable). La frontera es la hora del corte,
        así un movimiento del mismo día pero anterior al corte no se cuenta doble. */
    public function saldoCalculado(BankAccount $c): float
    {
        $corte = $c->corte_at?->toDateTimeString() ?? '2000-01-01 00:00:00';
        $movs = BankMovement::where('cuenta_id', $c->id)->where('created_at', '>=', $corte);
        $in = (clone $movs)->whereIn('tipo', ['entrada', 'aporte'])->sum('monto');
        $out = (clone $movs)->whereIn('tipo', ['salida', 'retiro'])->sum('monto');
        $tOut = BankMovement::where('tipo', 'traspaso')->where('cuenta_id', $c->id)->where('created_at', '>=', $corte)->sum('monto');
        $tIn = BankMovement::where('tipo', 'traspaso')->where('cuenta_destino_id', $c->id)->where('created_at', '>=', $corte)->sum('monto');
        return (float) $c->saldo_inicial + $in - $out - $tOut + $tIn;
    }
}
