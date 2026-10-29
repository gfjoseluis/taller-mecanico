<?php

namespace App\Application\Lecturas;

use App\Models\BankMovement;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

/** Lecturas de reportes con caché corta (agregados, nunca filas para sumar en PHP). */
class ReportesService
{
    public const SECCIONES = ['ventas', 'top', 'mecanicos', 'ganancia', 'bancos'];

    public function datos(string $seccion, string $desde, string $hasta): mixed
    {
        return cache()->remember("rep.{$seccion}.{$desde}.{$hasta}", 600, function () use ($seccion, $desde, $hasta) {
            $rango = fn ($q) => $q->whereDate('created_at', '>=', $desde)->whereDate('created_at', '<=', $hasta);
            return match ($seccion) {
                'ventas' => $rango(Payment::selectRaw("DATE_FORMAT(created_at,'%Y-%m') m, SUM(recibido) t"))->groupBy('m')->orderBy('m')->get(),
                'top' => $rango(WorkOrderLine::selectRaw('product_id, SUM(cantidad) cant, SUM(cantidad*precio) total')->whereNotNull('product_id'))->groupBy('product_id')->orderByDesc('cant')->limit(10)->with('product')->get(),
                'mecanicos' => $rango(WorkOrder::selectRaw('COALESCE(tecnico_id,0) tec, COUNT(*) n, SUM(total) t'))->groupBy('tec')->with('tecnico')->get(),
                'ganancia' => [
                    'ingresos' => $rango(Payment::selectRaw("DATE_FORMAT(created_at,'%Y-%m') m, SUM(recibido) t"))->groupBy('m')->orderBy('m')->pluck('t', 'm'),
                    'gastos' => $rango(Purchase::selectRaw("DATE_FORMAT(created_at,'%Y-%m') m, SUM(total) t"))->groupBy('m')->orderBy('m')->pluck('t', 'm'),
                ],
                'bancos' => $rango(BankMovement::selectRaw('cuenta_id, tipo, SUM(monto) t'))->groupBy('cuenta_id', 'tipo')->with('cuenta')->get(),
            };
        });
    }

    /** Encabezados del CSV por sección (punto y coma, compatible Excel español). */
    public function columnas(string $seccion): array
    {
        return match ($seccion) {
            'ventas' => ['Mes', 'Cobrado (Bs)'],
            'top' => ['SKU', 'Repuesto', 'Cantidad', 'Total (Bs)'],
            'mecanicos' => ['Mecánico', 'Órdenes', 'Total (Bs)'],
            'ganancia' => ['Mes', 'Ingresos (Bs)', 'Gastos (Bs)', 'Ganancia (Bs)'],
            'bancos' => ['Cuenta', 'Tipo', 'Total (Bs)'],
        };
    }

    /** Filas del CSV con valores simples (misma fuente que la pantalla). */
    public function filas(string $seccion, mixed $datos): array
    {
        return match ($seccion) {
            'ventas' => collect($datos)->map(fn ($d) => [$d->m, round((float) $d->t, 2)])->values()->all(),
            'top' => collect($datos)->map(fn ($d) => [$d->product->sku ?? '', $d->product->nombre ?? '', (float) $d->cant, round((float) $d->total, 2)])->values()->all(),
            'mecanicos' => collect($datos)->map(fn ($d) => [$d->tecnico->name ?? 'Sin asignar', (int) $d->n, round((float) $d->t, 2)])->values()->all(),
            'ganancia' => $this->filasGanancia($datos),
            'bancos' => collect($datos)->map(fn ($d) => [$d->cuenta->nombre ?? '', $d->tipo, round((float) $d->t, 2)])->values()->all(),
        };
    }

    private function filasGanancia(mixed $datos): array
    {
        $meses = array_unique(array_merge(array_keys((array) $datos['ingresos']), array_keys((array) $datos['gastos'])));
        sort($meses);
        $filas = [];
        foreach ($meses as $m) {
            $ing = (float) ($datos['ingresos'][$m] ?? 0);
            $gas = (float) ($datos['gastos'][$m] ?? 0);
            $filas[] = [$m, round($ing, 2), round($gas, 2), round($ing - $gas, 2)];
        }
        return $filas;
    }
}
