<?php

namespace App\Application\UseCases;

use App\Models\Product;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

/** Una sola responsabilidad: crear la orden con sus líneas, stock y auditoría. */
class CrearOrdenUseCase
{
    public function ejecutar(int $clientId, int $vehicleId, string $diagnostico, float $manoBase, array $servicios, array $lineas, ?int $parentId = null): WorkOrder
    {
        if (! Vehicle::where('id', $vehicleId)->where('client_id', $clientId)->exists()) {
            throw new \DomainException('El vehículo no pertenece al cliente elegido. Ej: agrega el vehículo al cliente.');
        }
        return DB::transaction(function () use ($clientId, $vehicleId, $diagnostico, $manoBase, $servicios, $lineas, $parentId) {
            $mano = $manoBase + (float) collect($servicios)->sum(fn ($s) => (float) ($s['precio'] ?? 0));
            $rep = (float) collect($lineas)->sum(fn ($l) => (float) ($l['cantidad'] ?? 0) * (float) ($l['precio'] ?? 0));
            $n = 'SRV-'.str_pad((string) (WorkOrder::max('id') + 1), 6, '0', STR_PAD_LEFT);
            $ot = WorkOrder::create([
                'numero' => $n, 'client_id' => $clientId, 'vehicle_id' => $vehicleId, 'parent_id' => $parentId,
                'diagnostico' => $diagnostico."\n".collect($servicios)->map(fn ($s) => '- '.($s['descripcion'] ?? '').' ('.($s['precio'] ?? 0).')')->implode("\n"),
                'mano_obra' => $mano, 'total_repuestos' => $rep, 'total' => $mano + $rep, 'estado' => 'registrada',
            ]);
            $this->aplicarLineas($ot, $lineas);
            $ot->logs()->create(['user_id' => auth()->id(), 'accion' => $parentId ? 'complementaria' : 'creada', 'detalle' => ['parent_id' => $parentId]]);
            return $ot;
        });
    }

    /** @param array{product_id:int,cantidad:float,precio:float}[] $lineas */
    public function aplicarLineas(WorkOrder $ot, array $lineas): void
    {
        foreach ($lineas as $l) {
            if (empty($l['product_id'])) continue;
            $ot->lineas()->create(['product_id' => $l['product_id'], 'cantidad' => (float) ($l['cantidad'] ?? 0), 'precio' => (float) ($l['precio'] ?? 0)]);
            Product::where('id', $l['product_id'])->decrement('stock', (float) ($l['cantidad'] ?? 0));
        }
    }
}
