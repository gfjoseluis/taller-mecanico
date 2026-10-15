<?php

namespace App\Domain\Pagos;

class FabricaMedios
{
    public static function crear(string $nombre): MedioCobro
    {
        return match ($nombre) {
            'efectivo' => new Efectivo,
            'transferencia' => new Transferencia,
            'credito' => new Credito,
            default => throw new \InvalidArgumentException("Medio desconocido: {$nombre}. Ej: efectivo."),
        };
    }
}
