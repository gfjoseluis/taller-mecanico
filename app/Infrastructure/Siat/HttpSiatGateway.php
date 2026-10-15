<?php

namespace App\Infrastructure\Siat;

use App\Domain\Contracts\FacturacionGatewayInterface;
use Illuminate\Support\Facades\Http;

class HttpSiatGateway implements FacturacionGatewayInterface
{
    public function emitir(array $payload): array
    {
        $res = Http::withToken(config('siat.token'))
            ->timeout(config('siat.timeout', 25))
            ->post(rtrim(config('siat.base_url'), '/').'/emitir', $payload);
        $res->throw();
        return $res->json();
    }

    public function anular(string $cuf, string $motivo): array
    {
        $res = Http::withToken(config('siat.token'))
            ->timeout(config('siat.timeout', 25))
            ->post(rtrim(config('siat.base_url'), '/').'/anular', compact('cuf', 'motivo'));
        $res->throw();
        return $res->json();
    }

    public function estado(string $cuf): array
    {
        $res = Http::withToken(config('siat.token'))
            ->timeout(config('siat.timeout', 25))
            ->get(rtrim(config('siat.base_url'), '/').'/estado/'.$cuf);
        $res->throw();
        return $res->json();
    }
}
