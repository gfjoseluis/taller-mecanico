<?php

namespace App\Application\UseCases;

use App\Models\Vehicle;

class GuardarVehiculoUseCase
{
    public function ejecutar(?int $id, array $d): Vehicle
    {
        if (Vehicle::where('placa', strtoupper($d['placa']))->when($id, fn ($q) => $q->where('id', '!=', $id))->exists()) {
            throw new \DomainException('Placa duplicada: '.$d['placa'].'. Ej: use otra placa.');
        }
        return Vehicle::updateOrCreate(['id' => $id], [
            'client_id' => $d['client_id'], 'placa' => strtoupper($d['placa']), 'marca' => $d['marca'] ?? '',
            'modelo' => $d['modelo'] ?? '', 'chasis' => $d['chasis'] ?? null, 'tonelaje' => $d['tonelaje'] ?? null,
        ]);
    }
}
