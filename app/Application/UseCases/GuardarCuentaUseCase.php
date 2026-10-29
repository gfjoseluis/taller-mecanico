<?php

namespace App\Application\UseCases;

use App\Models\BankAccount;

class GuardarCuentaUseCase
{
    public function ejecutar(?int $id, array $datos): void
    {
        if (! $id) {
            // Nace cuadrada: el inicial es el saldo actual y el corte parte hoy.
            $datos['saldo_inicial'] = $datos['saldo'] ?? 0;
            $datos['corte_at'] = now();
        } else {
            // Editar nunca toca dinero: solo datos (nombre, banco, número, moneda).
            unset($datos['saldo'], $datos['saldo_inicial'], $datos['corte_at']);
        }
        BankAccount::updateOrCreate(['id' => $id], $datos);
    }
}
