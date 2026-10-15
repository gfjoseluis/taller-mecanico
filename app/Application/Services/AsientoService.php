<?php

namespace App\Application\Services;

use App\Models\ChartAccount;
use App\Models\JournalEntry;
use App\Models\Periodo;
use Illuminate\Support\Facades\DB;

/** Registros contables: todo cuadra (debe = haber) o no se guarda. Mes cerrado = bloqueado. */
class AsientoService
{
    public function cuenta(string $concepto): ChartAccount
    {
        $codigo = app(SettingService::class)->get("contabilidad.mapa.{$concepto}", config("contabilidad.mapa.{$concepto}"));
        return ChartAccount::where('codigo', $codigo)->firstOrFail();
    }

    /** @param array{account_id:int,debe:float,haber:float}[] $lineas */
    public function registrar(string $glosa, string $modulo, ?string $referencia, array $lineas, string $fecha = null): JournalEntry
    {
        $fecha ??= now()->toDateString();
        if (Periodo::cerrado($fecha)) {
            throw new \RuntimeException('Mes cerrado: no se puede registrar. Ej: use el mes abierto o pida reapertura.');
        }
        $debe = round(collect($lineas)->sum(fn ($l) => (float) ($l['debe'] ?? 0)), 2);
        $haber = round(collect($lineas)->sum(fn ($l) => (float) ($l['haber'] ?? 0)), 2);
        if (abs($debe - $haber) > 0.005 || $debe <= 0) {
            throw new \RuntimeException("No cuadra: debe {$debe} vs haber {$haber}. Ej: ajuste los montos.");
        }
        return DB::transaction(function () use ($glosa, $modulo, $referencia, $lineas, $fecha) {
            $a = JournalEntry::create(['fecha' => $fecha, 'glosa' => $glosa, 'modulo' => $modulo, 'referencia' => $referencia, 'user_id' => auth()->id()]);
            foreach ($lineas as $l) $a->lines()->create($l);
            return $a;
        });
    }

    public function bancoCuenta(?int $bankAccountId): ChartAccount
    {
        // Caja chica (efectivo) va a su cuenta, el resto a bancos.
        if ($bankAccountId && \App\Models\BankAccount::where('id', $bankAccountId)->where('banco', 'Efectivo')->exists()) {
            return $this->cuenta('caja');
        }
        return $this->cuenta('bancos');
    }

    /** Venta de servicio: entra dinero (o queda por cobrar) + reconoce venta. */
    public function ventaServicio(float $totalRep, float $mano, string $medio, ?int $bankId, string $folio): void
    {
        $debito = $medio === 'credito' ? $this->cuenta('por_cobrar')->id : $this->bancoCuenta($bankId)->id;
        $this->registrar("Venta servicio {$folio}", 'ventas', $folio, [
            ['account_id' => $debito, 'debe' => $totalRep + $mano, 'haber' => 0],
            ['account_id' => $this->cuenta('ventas_repuestos')->id, 'debe' => 0, 'haber' => $totalRep],
            ['account_id' => $this->cuenta('servicios')->id, 'debe' => 0, 'haber' => $mano],
        ]);
    }

    public function abonoCliente(float $monto, ?int $bankId, string $folio): void
    {
        $this->registrar("Abono cliente {$folio}", 'ventas', $folio, [
            ['account_id' => $this->bancoCuenta($bankId)->id, 'debe' => $monto, 'haber' => 0],
            ['account_id' => $this->cuenta('por_cobrar')->id, 'debe' => 0, 'haber' => $monto],
        ]);
    }

    public function compra(float $total, string $tipo, ?int $bankId, string $folio): void
    {
        $credito = $tipo === 'credito' ? $this->cuenta('por_pagar')->id : $this->bancoCuenta($bankId)->id;
        $this->registrar("Compra {$folio}", 'compras', $folio, [
            ['account_id' => $this->cuenta('inventario')->id, 'debe' => $total, 'haber' => 0],
            ['account_id' => $credito, 'debe' => 0, 'haber' => $total],
        ]);
    }

    public function abonoProveedor(float $monto, ?int $bankId, string $folio): void
    {
        $this->registrar("Abono importadora {$folio}", 'compras', $folio, [
            ['account_id' => $this->cuenta('por_pagar')->id, 'debe' => $monto, 'haber' => 0],
            ['account_id' => $this->bancoCuenta($bankId)->id, 'debe' => 0, 'haber' => $monto],
        ]);
    }

    public function movimientoManual(string $tipo, float $monto, ?int $bankId, ?int $destinoId, ?int $contraId, string $motivo): void
    {
        $banco = fn ($id) => $this->bancoCuenta($id)->id;
        $lineas = match ($tipo) {
            'salida' => [
                ['account_id' => $contraId, 'debe' => $monto, 'haber' => 0],
                ['account_id' => $banco($bankId), 'debe' => 0, 'haber' => $monto],
            ],
            'entrada' => [
                ['account_id' => $banco($bankId), 'debe' => $monto, 'haber' => 0],
                ['account_id' => $contraId, 'debe' => 0, 'haber' => $monto],
            ],
            'traspaso' => [
                ['account_id' => $banco($destinoId), 'debe' => $monto, 'haber' => 0],
                ['account_id' => $banco($bankId), 'debe' => 0, 'haber' => $monto],
            ],
            'aporte' => [
                ['account_id' => $banco($bankId), 'debe' => $monto, 'haber' => 0],
                ['account_id' => $this->cuenta('capital')->id, 'debe' => 0, 'haber' => $monto],
            ],
            'retiro' => [
                ['account_id' => $this->cuenta('capital')->id, 'debe' => $monto, 'haber' => 0],
                ['account_id' => $banco($bankId), 'debe' => 0, 'haber' => $monto],
            ],
        };
        $this->registrar($motivo, 'tesoreria', null, $lineas);
    }
}
