<?php

namespace App\Application\UseCases;

use App\Models\Recepcion;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

/** Hoja de recepción: cómo llega el vehículo (solo en orden abierta). */
class GuardarRecepcionUseCase
{
    public function ejecutar(int $ordenId, string $combustible, array $inventario, ?string $otros, array $inspeccion, ?string $observaciones, ?int $kilometraje = null): Recepcion
    {
        return DB::transaction(function () use ($ordenId, $combustible, $inventario, $otros, $inspeccion, $observaciones, $kilometraje) {
            $ot = WorkOrder::findOrFail($ordenId);
            if ($ot->estado !== 'registrada') throw new \DomainException('Solo órdenes abiertas tienen recepción.');
            if (! array_key_exists($combustible, Recepcion::COMBUSTIBLE)) {
                throw new \DomainException('Nivel de combustible inválido.');
            }
            $inv = array_values(array_intersect($inventario, array_keys(Recepcion::INVENTARIO)));
            $ins = array_values(array_intersect($inspeccion, array_keys(Recepcion::INSPECCION)));
            return Recepcion::updateOrCreate(['work_order_id' => $ot->id], [
                'combustible' => $combustible, 'inventario' => $inv, 'inventario_otros' => $otros,
                'inspeccion' => $ins, 'observaciones' => $observaciones, 'kilometraje' => $kilometraje, 'user_id' => auth()->id(),
            ]);
        });
    }
}
