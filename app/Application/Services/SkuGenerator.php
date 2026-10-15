<?php

namespace App\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Generador de SKU amigable y concurrente.
 * Formato: PREFIJO-SUB-000123 ej: REP-FILT-000123
 */
class SkuGenerator
{
    public function generate(string $tipo, ?string $subcategoria = null): string
    {
        $prefijos = config('sku.prefijos');
        $prefijo = $prefijos[$tipo] ?? strtoupper(substr($tipo, 0, 3));
        $sub = $subcategoria
            ? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $subcategoria), 0, 4))
            : 'GEN';
        $sub = str_pad($sub, 4, 'X');

        $correlativo = DB::transaction(function () use ($tipo) {
            $row = DB::table('sku_counters')->where('tipo', $tipo)->lockForUpdate()->first();
            if (! $row) {
                DB::table('sku_counters')->insert(['tipo' => $tipo, 'ultimo' => 1]);
                return 1;
            }
            DB::table('sku_counters')->where('tipo', $tipo)->increment('ultimo');
            return $row->ultimo + 1;
        });

        $len = config('sku.longitud_correlativo', 6);
        $sep = config('sku.separador', '-');

        return $prefijo.$sep.$sub.$sep.Str::padLeft((string) $correlativo, $len, '0');
    }
}
