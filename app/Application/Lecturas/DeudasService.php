<?php

namespace App\Application\Lecturas;

use App\Models\Purchase;
use App\Models\WorkOrder;

/** Deudas operativas (no contabilidad formal): quién debe y a quién debemos. */
class DeudasService
{
    public function porCobrar()
    {
        return WorkOrder::with(['client', 'vehicle'])
            ->withSum('payments', 'recibido')
            ->where('estado', 'cerrada-pendiente')->orderBy('id', 'desc')->paginate(15);
    }

    public function porPagar()
    {
        return Purchase::with('supplier')->where('estado', 'vigente')->where('saldo', '>', 0)->orderBy('id', 'desc')->paginate(15);
    }
}
