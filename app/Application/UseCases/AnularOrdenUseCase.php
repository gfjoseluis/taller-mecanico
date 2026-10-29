<?php

namespace App\Application\UseCases;

use App\Models\Product;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class AnularOrdenUseCase
{
    public function ejecutar(int $id): void
    {
        DB::transaction(function () use ($id) {
            $ot = WorkOrder::with('lineas')->findOrFail($id);
            if ($ot->payments()->where('recibido', '>', 0)->exists()) {
                throw new \DomainException('No se puede anular: ya tiene cobros o adelantos. Cree una orden nueva para lo adicional.');
            }
            foreach ($ot->lineas as $l) if ($l->product_id) Product::where('id', $l->product_id)->increment('stock', $l->cantidad);
            $ot->update(['estado' => 'anulada']);
            $ot->logs()->create(['user_id' => auth()->id(), 'accion' => 'anulada', 'detalle' => []]);
        });
    }
}
