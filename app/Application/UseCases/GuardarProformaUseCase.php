<?php

namespace App\Application\UseCases;

use App\Models\Proforma;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

/** Pro-forma: presupuesto con precios pero sin stock ni dinero. */
class GuardarProformaUseCase
{
    public function ejecutar(?int $id, int $clientId, int $vehicleId, string $diagnostico, float $manoBase, array $servicios, array $lineas, float $descuento = 0): Proforma
    {
        if (! Vehicle::where('id', $vehicleId)->where('client_id', $clientId)->exists()) {
            throw new \DomainException('El vehículo no pertenece al cliente elegido. Ej: agrega el vehículo al cliente.');
        }
        return DB::transaction(function () use ($id, $clientId, $vehicleId, $diagnostico, $manoBase, $servicios, $lineas, $descuento) {
            $mano = $manoBase + (float) collect($servicios)->sum(fn ($s) => (float) ($s['precio'] ?? 0));
            $rep = (float) collect($lineas)->sum(fn ($l) => (float) ($l['cantidad'] ?? 0) * (float) ($l['precio'] ?? 0));
            if ($descuento < 0 || $descuento > $mano + $rep) {
                throw new \DomainException('Descuento inválido. Ej: entre 0 y '.number_format($mano + $rep, 2).'.');
            }
            $datos = [
                'client_id' => $clientId, 'vehicle_id' => $vehicleId,
                'diagnostico' => $diagnostico,
                'mano_obra' => $mano, 'total_repuestos' => $rep, 'descuento' => $descuento, 'total' => $mano + $rep - $descuento,
            ];
            if (! $id) {
                $datos['numero'] = 'PRO-'.str_pad((string) (Proforma::max('id') + 1), 6, '0', STR_PAD_LEFT);
                $datos['user_id'] = auth()->id();
            }
            $p = Proforma::updateOrCreate(['id' => $id], $datos);
            if ($id) $p->lineas()->delete();
            foreach ($servicios as $s) {
                $desc = trim((string) ($s['descripcion'] ?? ''));
                if ($desc === '') continue;
                $p->lineas()->create(['product_id' => null, 'tipo' => 'servicio', 'descripcion' => $desc, 'cantidad' => 1, 'precio' => (float) ($s['precio'] ?? 0)]);
            }
            foreach ($lineas as $l) {
                if (empty($l['product_id'])) continue;
                $p->lineas()->create(['product_id' => $l['product_id'], 'cantidad' => (float) ($l['cantidad'] ?? 0), 'precio' => (float) ($l['precio'] ?? 0)]);
            }
            return $p->fresh('lineas');
        });
    }
}
