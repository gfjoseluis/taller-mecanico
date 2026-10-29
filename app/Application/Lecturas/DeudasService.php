<?php

namespace App\Application\Lecturas;

use App\Models\Purchase;
use App\Models\WorkOrder;

/** Deudas operativas (no contabilidad formal): quién debe y a quién debemos. */
class DeudasService
{
    public function porCobrar(?string $buscar = null, bool $mora = false)
    {
        $s = trim((string) $buscar);
        $q = WorkOrder::with(['client', 'vehicle'])
            ->withSum('payments', 'recibido')
            ->where('estado', 'cerrada-pendiente')->orderBy('id', 'desc');
        if ($mora) $q->where('cerrada_at', '<', now()->subDays(15));
        if ($s !== '') {
            $q->where(fn ($w) => $w->where('numero', 'like', "%{$s}%")
                ->orWhereHas('client', fn ($c) => $c->where('nombre', 'like', "%{$s}%")));
        }
        return $q->paginate(15)->withQueryString();
    }

    public function porPagar(?string $buscar = null)
    {
        $s = trim((string) $buscar);
        $q = Purchase::with('supplier')->where('estado', 'vigente')->where('saldo', '>', 0)->orderBy('id', 'desc');
        if ($s !== '') {
            $q->where(fn ($w) => $w->where('folio', 'like', "%{$s}%")
                ->orWhereHas('supplier', fn ($p) => $p->where('nombre', 'like', "%{$s}%")));
        }
        return $q->paginate(15)->withQueryString();
    }
}
