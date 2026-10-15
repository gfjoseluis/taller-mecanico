<?php

namespace App\Application\UseCases;

use App\Models\Category;

class GuardarCategoriaUseCase
{
    public function ejecutar(?int $id, string $nombre, string $prefijo, string $tipo): void
    {
        Category::updateOrCreate(['id' => $id], ['nombre' => $nombre, 'prefijo' => strtoupper($prefijo), 'tipo' => $tipo]);
    }
}
