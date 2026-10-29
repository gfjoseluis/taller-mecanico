<?php

namespace App\Application\Concerns;

/** Los numéricos vacíos valen nulo: borrar para reescribir no dispara errores. */
trait NormalizaVacios
{
    public function vaciosANulo(string ...$props): void
    {
        foreach ($props as $p) {
            if (($this->{$p} ?? null) === '') $this->{$p} = null;
        }
    }
}
