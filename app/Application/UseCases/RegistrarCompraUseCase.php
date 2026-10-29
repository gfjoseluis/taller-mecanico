<?php

namespace App\Application\UseCases;

use App\Application\Services\AsientoService;
use App\Application\Services\MovimientoService;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class RegistrarCompraUseCase
{
    public function __construct(private MovimientoService $dinero, private AsientoService $contable) {}

    public function ejecutar(?int $supplierId, ?int $bankId, string $tipo, string $glosa, array $lineas): Purchase
    {
        return DB::transaction(function () use ($supplierId, $bankId, $tipo, $glosa, $lineas) {
            if ($tipo === 'credito' && ! $supplierId) {
                throw new \DomainException('A crédito el proveedor es obligatorio: la deuda necesita dueño.');
            }
            // Contado sin proveedor: compra local (ferretería, mercado) agrupada en genérico.
            $esLocal = $supplierId === null;
            $supplierId ??= \App\Models\Supplier::firstOrCreate(['nombre' => 'Compra local / mostrador'])->id;
            if ($esLocal && trim($glosa) === '') {
                throw new \DomainException('Anota dónde compraste. Ej: ferretería El Puente, filtros.');
            }
            $total = (float) collect($lineas)->sum(fn ($l) => (float) ($l['cantidad'] ?? 0) * (float) ($l['costo'] ?? 0));
            $folio = 'CMP-'.str_pad((string) (Purchase::max('id') + 1), 6, '0', STR_PAD_LEFT);
            $c = Purchase::create([
                'folio' => $folio, 'supplier_id' => $supplierId,
                'bank_account_id' => $tipo === 'contado' ? $bankId : null,
                'tipo_pago' => $tipo, 'total' => $total,
                'saldo' => $tipo === 'credito' ? $total : 0, 'glosa' => $glosa,
                'estado' => $tipo === 'contado' ? 'pagada' : 'vigente',
            ]);
            foreach ($lineas as $l) {
                if (empty($l['product_id'])) continue;
                $c->lines()->create(['product_id' => $l['product_id'], 'cantidad' => (float) ($l['cantidad'] ?? 0), 'costo' => (float) ($l['costo'] ?? 0)]);
                Product::where('id', $l['product_id'])->increment('stock', (float) ($l['cantidad'] ?? 0));
                Product::where('id', $l['product_id'])->update(['precio_compra' => (float) ($l['costo'] ?? 0)]);
            }
            if ($tipo === 'contado' && $bankId) {
                $this->dinero->registrar(['tipo' => 'salida', 'cuenta_id' => $bankId, 'monto' => $total, 'motivo' => "Compra {$folio}", 'origen_tipo' => 'compra', 'origen_id' => $c->id]);
            }
            $this->contable->compra($total, $tipo, $bankId, $folio);
            return $c;
        });
    }
}
