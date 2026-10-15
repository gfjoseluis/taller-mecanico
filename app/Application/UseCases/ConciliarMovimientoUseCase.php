<?php

namespace App\Application\UseCases;

use App\Models\BankMovement;

class ConciliarMovimientoUseCase
{
    public function alternar(int $id): void
    {
        $m = BankMovement::findOrFail($id);
        $m->update(['revisado' => ! $m->revisado]);
    }
}
