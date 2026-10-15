<?php

namespace App\Application\UseCases;

use App\Models\BankAccount;

class GuardarCuentaUseCase
{
    public function ejecutar(?int $id, array $datos): void
    {
        BankAccount::updateOrCreate(['id' => $id], $datos);
    }
}
