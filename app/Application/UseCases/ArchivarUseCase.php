<?php

namespace App\Application\UseCases;

use Illuminate\Database\Eloquent\Model;

/** Borrado lógico y restauración (una sola responsabilidad transversal). */
class ArchivarUseCase
{
    /** @param class-string<Model> $modelo */
    public function eliminar(string $modelo, int $id): void
    {
        $modelo::findOrFail($id)->delete();
    }

    /** @param class-string<Model> $modelo */
    public function restaurar(string $modelo, int $id): void
    {
        $modelo::withTrashed()->findOrFail($id)->restore();
    }
}
