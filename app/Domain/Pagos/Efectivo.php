<?php

namespace App\Domain\Pagos;

use App\Application\Services\MovimientoService;

class Efectivo implements MedioCobro
{
    public function nombre(): string { return 'efectivo'; }

    public function cuentaDestino(?int $bancoId): ?int
    {
        return app(MovimientoService::class)->caja()->id;
    }

    public function recibido(float $total, float $ingresado): float { return $ingresado; }
    public function vuelto(float $total, float $ingresado): float { return max(0, $ingresado - $total); }
    public function deuda(float $total, float $ingresado): float { return 0; }
}
