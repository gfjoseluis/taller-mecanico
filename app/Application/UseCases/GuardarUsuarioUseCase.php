<?php

namespace App\Application\UseCases;

use App\Models\User;

class GuardarUsuarioUseCase
{
    public function ejecutar(?int $id, array $datos, array $roles): void
    {
        User::updateOrCreate(['id' => $id], $datos)->syncRoles($roles);
    }
}
