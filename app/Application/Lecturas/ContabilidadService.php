<?php

namespace App\Application\Lecturas;

use App\Models\ChartAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;

/** Agregados contables fuera de las rutas. */
class ContabilidadService
{
    public function diario(?string $desde, ?string $hasta)
    {
        $q = JournalEntry::with('lines.account')->latest();
        if ($desde) $q->where('fecha', '>=', $desde);
        if ($hasta) $q->where('fecha', '<=', $hasta);
        return $q->paginate(15);
    }

    public function filasDiario(): array
    {
        $filas = [];
        foreach (JournalEntry::with('lines.account')->latest()->limit(2000)->cursor() as $a) {
            foreach ($a->lines as $l) $filas[] = [$a->fecha, $a->glosa, $a->modulo, $l->account->codigo ?? '', $l->debe, $l->haber];
        }
        return $filas;
    }

    public function filasBalance(): array
    {
        $filas = [];
        foreach (JournalLine::with('account')->cursor() as $l) {
            $filas[] = [$l->account->codigo ?? '', $l->account->nombre ?? '', $l->debe, $l->haber];
        }
        return $filas;
    }
    public function mayor(?int $cuentaId): array
    {
        $q = JournalLine::with('account')->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_id');
        if ($cuentaId) $q->where('account_id', $cuentaId);
        $resumen = $q->select('journal_lines.*')->get()->groupBy('account_id')->map(function ($ls) {
            $a = $ls->first()->account;
            $debe = $ls->sum('debe');
            $haber = $ls->sum('haber');
            return ['codigo' => $a->codigo ?? '', 'nombre' => $a->nombre ?? '', 'debe' => $debe, 'haber' => $haber, 'saldo' => in_array($a->tipo ?? '', ['activo', 'gasto']) ? $debe - $haber : $haber - $debe];
        })->sortBy('codigo')->values();
        return ['resumen' => $resumen, 'cuentas' => ChartAccount::orderBy('codigo')->get()];
    }

    public function tipos(): array
    {
        $tipos = ['activo' => 0, 'pasivo' => 0, 'patrimonio' => 0, 'ingreso' => 0, 'gasto' => 0];
        foreach (JournalLine::with('account')->cursor() as $l) {
            $t = $l->account->tipo ?? 'gasto';
            $tipos[$t] += in_array($t, ['activo', 'gasto']) ? ($l->debe - $l->haber) : ($l->haber - $l->debe);
        }
        return $tipos;
    }

    public function balance(): array
    {
        $t = $this->tipos();
        return [
            'filas' => [
                ['nombre' => 'Lo que tienes (activo)', 'monto' => $t['activo']],
                ['nombre' => 'Lo que debes (pasivo)', 'monto' => $t['pasivo']],
                ['nombre' => 'Capital de socios (patrimonio)', 'monto' => $t['patrimonio']],
            ],
            'nota' => 'Cuadra si: activo = pasivo + patrimonio + ganancia. Diferencia: '.number_format($t['activo'] - $t['pasivo'] - $t['patrimonio'] - ($t['ingreso'] - $t['gasto']), 2),
        ];
    }

    public function resultados(): array
    {
        $t = $this->tipos();
        return [
            'filas' => [
                ['nombre' => 'Entradas por ventas y servicios (ingresos)', 'monto' => $t['ingreso']],
                ['nombre' => 'Salidas y costos (gastos)', 'monto' => $t['gasto']],
                ['nombre' => 'Ganancia (ingresos − gastos)', 'monto' => $t['ingreso'] - $t['gasto']],
            ],
            'nota' => 'Ganancia = ingresos − gastos del período.',
        ];
    }
}
