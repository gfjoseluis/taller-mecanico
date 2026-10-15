<?php

namespace App\Domain\Pagos;

/** OCP: cada medio sabe cobrarse; agregar uno nuevo no toca el caso de uso. */
interface MedioCobro
{
    public function nombre(): string; // efectivo|transferencia|credito
    public function cuentaDestino(?int $bancoId): ?int; // null = no mueve dinero
    public function recibido(float $total, float $ingresado): float;
    public function vuelto(float $total, float $ingresado): float;
    public function deuda(float $total, float $ingresado): float;
}
