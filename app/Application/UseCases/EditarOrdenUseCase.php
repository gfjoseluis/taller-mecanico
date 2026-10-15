<?php

namespace App\Application\UseCases;

use App\Models\Product;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class EditarOrdenUseCase
{
    public function __construct(private CrearOrdenUseCase $crear) {}

    public function ejecutar(int $id, string $diagnostico, float $manoBase, array $servicios, array $lineas): WorkOrder
    {
        return DB::transaction(function () use ($id, $diagnostico, $manoBase, $servicios, $lineas) {
            $ot = WorkOrder::with('lineas')->findOrFail($id);
            if (in_array($ot->estado, ['cerrada', 'cerrada-pagada', 'cerrada-pendiente', 'anulada'], true)) {
                throw new \DomainException('Orden cerrada/anulada: use complementaria.');
            }
            $antes = ['total' => (float) $ot->total, 'mano_obra' => (float) $ot->mano_obra, 'repuestos' => (float) $ot->total_repuestos];
            foreach ($ot->lineas as $l) Product::where('id', $l->product_id)->increment('stock', $l->cantidad);
            $ot->lineas()->delete();
            $mano = $manoBase + (float) collect($servicios)->sum(fn ($s) => (float) ($s['precio'] ?? 0));
            $rep = (float) collect($lineas)->sum(fn ($l) => (float) ($l['cantidad'] ?? 0) * (float) ($l['precio'] ?? 0));
            $ot->update([
                'diagnostico' => $diagnostico."\n".collect($servicios)->map(fn ($s) => '- '.($s['descripcion'] ?? '').' ('.($s['precio'] ?? 0).')')->implode("\n"),
                'mano_obra' => $mano, 'total_repuestos' => $rep, 'total' => $mano + $rep,
            ]);
            $this->crear->aplicarLineas($ot->fresh('lineas'), $lineas);
            $ot->logs()->create(['user_id' => auth()->id(), 'accion' => 'editada', 'detalle' => ['antes' => $antes, 'ahora' => ['total' => $mano + $rep]]]);
            return $ot->fresh();
        });
    }
}
