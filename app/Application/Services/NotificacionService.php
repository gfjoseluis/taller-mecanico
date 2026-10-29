<?php

namespace App\Application\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Cache;

/** La campana: los problemas buscan al usuario (caché 10 min, por rol). */
class NotificacionService
{
    public function avisos(): array
    {
        $u = auth()->user();
        if (! $u) return ['conteo' => 0, 'lista' => []];
        return Cache::remember('avisos.'.$u->id, 600, function () use ($u) {
            $lista = [];
            if ($u->can('productos.ver')) {
                $bajo = Product::bajoStock()->count();
                if ($bajo) $lista[] = ['icono' => '📦', 'titulo' => "{$bajo} repuestos con stock bajo", 'detalle' => 'Ej: revisa mínimos en Inventario.', 'url' => '/productos?solo_bajo=1'];
            }
            if ($u->can('ots.ver')) {
                $ab = WorkOrder::where('estado', 'registrada')->where('created_at', '<', now()->subDays(7))->count();
                if ($ab) $lista[] = ['icono' => '🛠️', 'titulo' => "{$ab} órdenes llevan +7 días abiertas", 'detalle' => 'Ej: vehículos olvidados, cerrar o cobrar.', 'url' => '/servicios?antiguas=1'];
            }
            if ($u->can('ventas.ver')) {
                $n = WorkOrder::where('estado', 'cerrada-pendiente')->where('cerrada_at', '<', now()->subDays(15))->count();
                if ($n) $lista[] = ['icono' => '💰', 'titulo' => "{$n} deudas de clientes con 15+ días", 'detalle' => 'Ej: abonar en Por cobrar.', 'url' => '/por-cobrar?mora=1'];
            }
            if ($u->can('compras.ver')) {
                $n = Purchase::where('estado', 'vigente')->where('saldo', '>', 0)->where('created_at', '<', now()->subDays(7))->count();
                if ($n) $lista[] = ['icono' => '🏦', 'titulo' => "{$n} deudas con importadoras de +7 días", 'detalle' => 'Ej: abonar en Por pagar.', 'url' => '/por-pagar'];
            }
            if ($u->can('config.editar')) {
                $ultimo = app(\App\Application\Services\RespaldoService::class)->ultimo();
                if (! $ultimo || $ultimo->lt(now()->subDays(2))) {
                    $lista[] = ['icono' => '💾', 'titulo' => 'Sin respaldo reciente (+2 días)', 'detalle' => 'Ej: genera uno en Respaldos.', 'url' => '/respaldos'];
                }
            }
            if ($u->can('contabilidad.ver')) {
                $p = \App\Models\Periodo::where('gestion', now()->year)->where('mes', now()->month)->first();
                if (! $p || ! $p->cerrado) {
                    $mes = now()->locale('es')->monthName;
                    $lista[] = ['icono' => '📒', 'titulo' => "El mes ({$mes}) sigue abierto", 'detalle' => 'Ej: cerrar en Contabilidad → Cierre.', 'url' => '/contabilidad/cierre'];
                }
            }
            return ['conteo' => count($lista), 'lista' => $lista];
        });
    }

    public static function olvidar(?int $userId = null): void
    {
        Cache::forget('avisos.'.($userId ?? auth()->id()));
    }
}
