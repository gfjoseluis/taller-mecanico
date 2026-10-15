<?php

namespace App\Domain\Pagos;

class Transferencia implements MedioCobro
{
    public function nombre(): string { return 'transferencia'; }
    public function cuentaDestino(?int $bancoId): ?int { return $bancoId; }
    public function recibido(float $total, float $ingresado): float { return $ingresado; }
    public function vuelto(float $total, float $ingresado): float { return max(0, $ingresado - $total); }
    public function deuda(float $total, float $ingresado): float { return 0; }
}
