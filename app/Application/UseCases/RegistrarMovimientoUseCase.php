<?php

namespace App\Application\UseCases;

use App\Application\Services\AsientoService;
use App\Application\Services\MovimientoService;
use App\Models\BankMovement;

class RegistrarMovimientoUseCase
{
    public function __construct(private MovimientoService $dinero, private AsientoService $contable) {}

    public function ejecutar(string $tipo, int $cuentaId, ?int $destinoId, ?int $socioId, ?int $contraId, float $monto, string $motivo): BankMovement
    {
        $mov = $this->dinero->registrar([
            'tipo' => $tipo, 'cuenta_id' => $cuentaId, 'cuenta_destino_id' => $destinoId,
            'partner_id' => $socioId, 'monto' => $monto, 'motivo' => $motivo,
        ]);
        $this->contable->movimientoManual($tipo, $monto, $cuentaId, $destinoId, $contraId, $motivo." (mov #{$mov->id})");
        return $mov;
    }
}
