<?php

namespace App\Application\UseCases;

use Spatie\Permission\Models\Role;

class GuardarRolUseCase
{
    public function ejecutar(?int $id, string $nombre, array $permisos): void
    {
        Role::updateOrCreate(['id' => $id], ['name' => $nombre])->syncPermissions($permisos);
    }
}
