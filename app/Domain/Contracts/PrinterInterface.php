<?php

namespace App\Domain\Contracts;

interface PrinterInterface
{
    public function comprobante(string $tipo, int $id, string $destino = 'pdf'): mixed;
}
