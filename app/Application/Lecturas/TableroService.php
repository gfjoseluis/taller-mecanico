<?php

namespace App\Application\Lecturas;

use App\Models\BankAccount;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\WorkOrder;

/** Una sola responsabilidad: datos del tablero (las rutas no agregan). */
class TableroService
{
    public function datos(): array
    {
        $porCobrar = WorkOrder::where('estado', 'cerrada-pendiente')->sum('total')
            - Payment::whereHas('order', fn ($q) => $q->where('estado', 'cerrada-pendiente'))->sum('recibido');
        $ventas14 = Payment::selectRaw('DATE(created_at) d, SUM(recibido) t')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())->groupBy('d')->orderBy('d')->pluck('t', 'd');
        $labels14 = $datos14 = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $labels14[] = substr($d, 5);
            $datos14[] = (float) ($ventas14[$d] ?? 0);
        }
        $porEstado = WorkOrder::selectRaw('estado, COUNT(*) n')->groupBy('estado')->pluck('n', 'estado');
        return [
            'abiertas' => WorkOrder::where('estado', 'registrada')->count(),
            'ventasHoy' => Invoice::whereDate('created_at', today())->where('estado', 'validada')->sum('total'),
            'stockBajo' => Product::bajoStock()->count(),
            'saldoBancos' => BankAccount::sum('saldo'),
            'porCobrar' => max(0, $porCobrar),
            'porPagar' => Purchase::where('estado', 'vigente')->sum('saldo'),
            'ultimas' => WorkOrder::with('client')->latest()->limit(10)->get(),
            'labels14' => $labels14, 'datos14' => $datos14,
            'estadosLabels' => $porEstado->keys()->values(), 'estadosDatos' => $porEstado->values(),
        ];
    }
}
