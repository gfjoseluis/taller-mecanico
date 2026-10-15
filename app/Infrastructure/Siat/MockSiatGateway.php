<?php

namespace App\Infrastructure\Siat;

use App\Domain\Contracts\FacturacionGatewayInterface;
use Illuminate\Support\Str;

/** Mock para operar sin gateway real + cola offline. */
class MockSiatGateway implements FacturacionGatewayInterface
{
    public function emitir(array $payload): array
    {
        $cuf = strtoupper(Str::random(8)).'-MOCK-'.time();
        return [
            'cuf' => $cuf,
            'numero' => random_int(1, 999999),
            'qr' => 'https://piloto.siat.mock/qr/'.$cuf,
            'estado' => 'VALIDADA',
            'raw' => ['mock' => true, 'payload' => $payload],
        ];
    }

    public function anular(string $cuf, string $motivo): array
    {
        return ['estado' => 'ANULADA', 'raw' => ['cuf' => $cuf, 'motivo' => $motivo]];
    }

    public function estado(string $cuf): array
    {
        return ['estado' => 'VALIDADA', 'cuf' => $cuf];
    }
}
