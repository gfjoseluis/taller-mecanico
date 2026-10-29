<?php

namespace App\Application\Lecturas;

use App\Models\Purchase;

/** Detalle de compra para pantalla e impresión (misma fuente, mismo dato). */
class CompraDetalleService
{
    public function cargar(int $id): Purchase
    {
        return Purchase::with(['supplier', 'lines.product', 'abonos.banco'])->findOrFail($id);
    }
}
