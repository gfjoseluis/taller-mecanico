<?php

namespace App\Application\UseCases;

use App\Models\Supplier;

class GuardarProveedorUseCase
{
    public function ejecutar(?int $id, array $datos): Supplier
    {
        return Supplier::updateOrCreate(['id' => $id], $datos);
    }
}
