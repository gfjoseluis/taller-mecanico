<?php

namespace App\Domain\Pagos;

class Credito implements MedioCobro
{
    public function nombre(): string { return 'credito'; }
    public function cuentaDestino(?int $bancoId): ?int { return null; } // no mueve dinero
    public function recibido(float $total, float $ingresado): float { return 0; }
    public function vuelto(float $total, float $ingresado): float { return 0; }
    public function deuda(float $total, float $ingresado): float { return $total; }
}
