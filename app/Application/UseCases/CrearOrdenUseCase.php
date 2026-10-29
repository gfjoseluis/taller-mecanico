<?php

namespace App\Application\UseCases;

use App\Models\Product;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

/** Una sola responsabilidad: crear la orden con sus líneas, stock y auditoría. */
class CrearOrdenUseCase
{
    public function ejecutar(int $clientId, int $vehicleId, string $diagnostico, float $manoBase, array $servicios, array $lineas, ?int $parentId = null, float $descuento = 0, ?int $proformaId = null): WorkOrder
    {
        if (! Vehicle::where('id', $vehicleId)->where('client_id', $clientId)->exists()) {
            throw new \DomainException('El vehículo no pertenece al cliente elegido. Ej: agrega el vehículo al cliente.');
        }
        return DB::transaction(function () use ($clientId, $vehicleId, $diagnostico, $manoBase, $servicios, $lineas, $parentId, $descuento, $proformaId) {
            $mano = $manoBase + (float) collect($servicios)->sum(fn ($s) => (float) ($s['precio'] ?? 0));
            $rep = (float) collect($lineas)->sum(fn ($l) => (float) ($l['cantidad'] ?? 0) * (float) ($l['precio'] ?? 0));
            if ($descuento < 0 || $descuento > $mano + $rep) {
                throw new \DomainException('Descuento inválido. Ej: entre 0 y '.number_format($mano + $rep, 2).'.');
            }
            $n = 'SRV-'.str_pad((string) (WorkOrder::max('id') + 1), 6, '0', STR_PAD_LEFT);
            $ot = WorkOrder::create([
                'numero' => $n, 'client_id' => $clientId, 'vehicle_id' => $vehicleId, 'parent_id' => $parentId, 'proforma_id' => $proformaId,
                'diagnostico' => $diagnostico,
                'mano_obra' => $mano, 'total_repuestos' => $rep, 'descuento' => $descuento, 'total' => $mano + $rep - $descuento, 'estado' => 'registrada',
            ]);
            $this->aplicarServicios($ot, $servicios);
            $this->aplicarLineas($ot, $lineas);
            $ot->logs()->create(['user_id' => auth()->id(), 'accion' => $proformaId ? 'desde-proforma' : 'creada', 'detalle' => ['proforma_id' => $proformaId]]);
            return $ot;
        });
    }

    /** @param array{descripcion:string,precio:float}[] $servicios */
    public function aplicarServicios(WorkOrder $ot, array $servicios): void
    {
        foreach ($servicios as $s) {
            $desc = trim((string) ($s['descripcion'] ?? ''));
            if ($desc === '') continue;
            $ot->lineas()->create([
                'product_id' => null, 'tipo' => 'servicio', 'descripcion' => $desc,
                'cantidad' => 1, 'precio' => (float) ($s['precio'] ?? 0),
            ]);
        }
    }

    /** @param array{product_id:int,cantidad:float,precio:float}[] $lineas */
    public function aplicarLineas(WorkOrder $ot, array $lineas): void
    {
        foreach ($lineas as $l) {
            if (empty($l['product_id'])) continue;
            $p = Product::where('id', $l['product_id'])->first();
            $cant = (float) ($l['cantidad'] ?? 0);
            if (! $p || (float) $p->stock < $cant) {
                throw new \DomainException('Sin stock suficiente de '.($p->nombre ?? 'repuesto').' (hay '.number_format((float) ($p->stock ?? 0), 2).'). Ej: compra primero o baja la cantidad.');
            }
            $ot->lineas()->create(['product_id' => $l['product_id'], 'cantidad' => $cant, 'precio' => (float) ($l['precio'] ?? 0)]);
            Product::where('id', $l['product_id'])->decrement('stock', $cant);
        }
    }
}
