<?php

namespace App\Application\UseCases;

use App\Models\Product;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class EditarOrdenUseCase
{
    public function __construct(private CrearOrdenUseCase $crear) {}

    public function ejecutar(int $id, string $diagnostico, float $manoBase, array $servicios, array $lineas, float $descuento = 0): WorkOrder
    {
        return DB::transaction(function () use ($id, $diagnostico, $manoBase, $servicios, $lineas, $descuento) {
            $ot = WorkOrder::with('lineas')->findOrFail($id);
            if (in_array($ot->estado, ['cerrada', 'cerrada-pagada', 'cerrada-pendiente', 'anulada'], true)) {
                throw new \DomainException('Orden cerrada o anulada: cree una orden nueva.');
            }
            $antes = ['total' => (float) $ot->total, 'mano_obra' => (float) $ot->mano_obra, 'repuestos' => (float) $ot->total_repuestos, 'descuento' => (float) $ot->descuento];
            foreach ($ot->lineas as $l) if ($l->product_id) Product::where('id', $l->product_id)->increment('stock', $l->cantidad);
            $ot->lineas()->delete();
            $mano = $manoBase + (float) collect($servicios)->sum(fn ($s) => (float) ($s['precio'] ?? 0));
            $rep = (float) collect($lineas)->sum(fn ($l) => (float) ($l['cantidad'] ?? 0) * (float) ($l['precio'] ?? 0));
            if ($descuento < 0 || $descuento > $mano + $rep) {
                throw new \DomainException('Descuento inválido. Ej: entre 0 y '.number_format($mano + $rep, 2).'.');
            }
            $adelantado = (float) $ot->payments()->sum('recibido');
            if ($mano + $rep - $descuento < $adelantado) {
                throw new \DomainException('El total no puede bajar de lo adelantado ('.number_format($adelantado, 2).').');
            }
            $ot->update([
                'diagnostico' => $diagnostico,
                'mano_obra' => $mano, 'total_repuestos' => $rep, 'descuento' => $descuento, 'total' => $mano + $rep - $descuento,
            ]);
            $this->crear->aplicarServicios($ot->fresh('lineas'), $servicios);
            $this->crear->aplicarLineas($ot->fresh('lineas'), $lineas);
            $ot->logs()->create(['user_id' => auth()->id(), 'accion' => 'editada', 'detalle' => ['antes' => $antes, 'ahora' => ['total' => $mano + $rep - $descuento]]]);
            return $ot->fresh();
        });
    }
}
