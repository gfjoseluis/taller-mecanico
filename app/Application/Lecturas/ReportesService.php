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
            return match ($seccion) {
                'ventas' => Payment::selectRaw("DATE_FORMAT(created_at,'%Y-%m') m, SUM(recibido) t")->whereDate('created_at', '>=', $desde)->whereDate('created_at', '<=', $hasta)->groupBy('m')->orderBy('m')->get(),
                'top' => WorkOrderLine::selectRaw('product_id, SUM(cantidad) cant, SUM(cantidad*precio) total')->groupBy('product_id')->orderByDesc('cant')->limit(10)->with('product')->get(),
                'mecanicos' => WorkOrder::selectRaw('COALESCE(tecnico_id,0) tec, COUNT(*) n, SUM(total) t')->groupBy('tec')->with('tecnico')->get(),
                'ganancia' => [
                    'ingresos' => Payment::selectRaw("DATE_FORMAT(created_at,'%Y-%m') m, SUM(recibido) t")->whereDate('created_at', '>=', $desde)->whereDate('created_at', '<=', $hasta)->groupBy('m')->orderBy('m')->pluck('t', 'm'),
                    'gastos' => Purchase::selectRaw("DATE_FORMAT(created_at,'%Y-%m') m, SUM(total) t")->whereDate('created_at', '>=', $desde)->whereDate('created_at', '<=', $hasta)->groupBy('m')->orderBy('m')->pluck('t', 'm'),
                ],
                'bancos' => BankMovement::selectRaw('cuenta_id, tipo, SUM(monto) t')->groupBy('cuenta_id', 'tipo')->with('cuenta')->get(),
            };
        });
    }

    public function filasCsv(string $seccion, mixed $datos): array
    {
        $filas = [];
        foreach ((array) $datos as $r) $filas[] = (array) json_decode(json_encode($r), true);
        if ($seccion === 'ganancia') {
            foreach ($datos['ingresos'] as $m => $t) $filas[] = [$m, $t, $datos['gastos'][$m] ?? 0];
        }
        return $filas;
    }
}
