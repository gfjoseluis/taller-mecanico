<?php

namespace App\Application\UseCases;

use App\Application\Services\AsientoService;
use App\Application\Services\MovimientoService;
use App\Models\BankMovement;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class AnularCompraUseCase
{
    public function __construct(private MovimientoService $dinero) {}

    public function ejecutar(int $id): void
    {
        DB::transaction(function () use ($id) {
            $c = Purchase::with('lines')->findOrFail($id);
            foreach ($c->lines as $l) Product::where('id', $l->product_id)->decrement('stock', $l->cantidad);
            $mov = BankMovement::where('origen_tipo', 'compra')->where('origen_id', $c->id)->first();
            if ($mov) $this->dinero->revertir($mov, "Anulación compra {$c->folio}");
            $c->update(['estado' => 'anulada']);
        });
    }
}
