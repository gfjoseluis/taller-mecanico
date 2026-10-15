<?php

namespace App\Application\Concerns;

use App\Models\Product;

/** Un solo lugar para el evento del buscador (antes duplicado en 9 componentes). */
trait ManejaSeleccionados
{
    public function alElegirProducto(string $propLineas, $modelo, $id, $indice, string $campoPrecio): void
    {
        if ($modelo !== 'Product' || $indice === null || ! $id) return;
        $this->{$propLineas}[$indice]['product_id'] = $id;
        $p = Product::find($id);
        if ($p && empty($this->{$propLineas}[$indice][$campoPrecio])) {
            $base = $campoPrecio === 'precio' ? $p->precio_venta : $p->precio_compra;
            $this->{$propLineas}[$indice][$campoPrecio] = (float) $base;
        }
    }
}
