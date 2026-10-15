<?php

namespace App\Application\UseCases;

use App\Application\Services\AsientoService;
use App\Application\Services\MovimientoService;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class AbonarCompraUseCase
{
    public function __construct(private MovimientoService $dinero, private AsientoService $contable) {}

    public function ejecutar(int $compraId, string $medio, ?int $bancoId, float $monto): void
    {
        DB::transaction(function () use ($compraId, $medio, $bancoId, $monto) {
            $c = Purchase::findOrFail($compraId);
            if ($c->estado !== 'vigente' || (float) $c->saldo <= 0) throw new \DomainException('Solo compras con deuda.');
            if ($monto <= 0 || $monto > (float) $c->saldo) throw new \DomainException('Monto inválido.');
            $c->abonos()->create(['medio' => $medio, 'bank_account_id' => $medio === 'transferencia' ? $bancoId : null, 'monto' => $monto, 'user_id' => auth()->id()]);
            $saldo = (float) $c->saldo - $monto;
            $c->update(['saldo' => max(0, $saldo)] + ($saldo <= 0 ? ['estado' => 'pagada'] : []));
            $cuenta = $medio === 'transferencia' ? $bancoId : $this->dinero->caja()->id;
            $this->dinero->registrar(['tipo' => 'salida', 'cuenta_id' => $cuenta, 'monto' => $monto, 'motivo' => "Abono compra {$c->folio}", 'origen_tipo' => 'abono-proveedor', 'origen_id' => $c->id]);
            $this->contable->abonoProveedor($monto, $cuenta, $c->folio);
        });
    }
}
