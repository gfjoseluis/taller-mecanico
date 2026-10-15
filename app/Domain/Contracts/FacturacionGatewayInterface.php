<?php

namespace App\Domain\Contracts;

interface FacturacionGatewayInterface
{
    /** @return array{cuf:string, numero:int, qr:string, estado:string, raw:array} */
    public function emitir(array $payload): array;

    /** @return array{estado:string, raw:array} */
    public function anular(string $cuf, string $motivo): array;

    public function estado(string $cuf): array;
}
