<?php

namespace App\Application\UseCases;

use App\Models\Partner;

class GuardarSocioUseCase
{
    public function ejecutar(?int $id, array $datos): void
    {
        Partner::updateOrCreate(['id' => $id], $datos);
    }
}
