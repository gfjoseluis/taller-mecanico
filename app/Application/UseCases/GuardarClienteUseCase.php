<?php

namespace App\Application\UseCases;

use App\Models\Client;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

/** Cliente con su detalle de vehículos en una transacción (placa única). */
class GuardarClienteUseCase
{
    public function ejecutar(?int $id, array $datos, array $vehiculos): Client
    {
        foreach ($vehiculos as $v) {
            if (empty($v['placa'])) continue;
            $q = Vehicle::where('placa', strtoupper($v['placa']));
            if (! empty($v['id'])) $q->where('id', '!=', $v['id']);
            if ($q->exists()) throw new \DomainException('Placa duplicada: '.$v['placa'].'. Ej: use otra placa.');
        }
        return DB::transaction(function () use ($id, $datos, $vehiculos) {
            $c = Client::updateOrCreate(['id' => $id], $datos);
            $ids = [];
            foreach ($vehiculos as $v) {
                if (empty($v['placa'])) continue;
                $veh = $c->vehicles()->updateOrCreate(['id' => $v['id'] ?? null], [
                    'placa' => strtoupper($v['placa']), 'marca' => $v['marca'] ?? '', 'modelo' => $v['modelo'] ?? '',
                    'chasis' => $v['chasis'] ?? null, 'tonelaje' => $v['tonelaje'] ?? null,
                ]);
                $ids[] = $veh->id;
            }
            if ($id) $c->vehicles()->whereNotIn('id', $ids ?: [0])->delete();
            return $c;
        });
    }
}
