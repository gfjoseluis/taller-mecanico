<?php

namespace App\Support;

/** Monto en palabras para comprobantes (Ej: 2515.00 → "Dos mil quinientos quince 00/100 Bs"). */
class NumeroLetras
{
    private const UNIDADES = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve', 'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve', 'veinte'];
    private const DECENAS = ['', '', '', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    private const CENTENAS = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

    public static function convertir(float $monto, string $moneda = 'Bs'): string
    {
        $entero = (int) floor($monto);
        $centavos = str_pad((string) (int) round(($monto - $entero) * 100), 2, '0', STR_PAD_LEFT);
        return ucfirst(trim(self::parte($entero)))." {$centavos}/100 {$moneda}";
    }

    private static function parte(int $n): string
    {
        if ($n === 0) return 'cero';
        if ($n === 100) return 'cien';
        $out = '';
        if ($n >= 1000000) {
            $m = intdiv($n, 1000000);
            $out .= ($m === 1 ? 'un millón' : self::parte($m).' millones').' ';
            $n %= 1000000;
        }
        if ($n >= 1000) {
            $m = intdiv($n, 1000);
            $out .= ($m === 1 ? 'mil' : self::parte($m).' mil').' ';
            $n %= 1000;
        }
        if ($n >= 100) {
            $out .= self::CENTENAS[intdiv($n, 100)].' ';
            $n %= 100;
        }
        if ($n <= 20) {
            $out .= self::UNIDADES[$n];
        } else {
            $d = intdiv($n, 10);
            $u = $n % 10;
            $out .= $u === 0 ? self::DECENAS[$d] : ($d === 2 ? 'veinti'.self::UNIDADES[$u] : self::DECENAS[$d].' y '.self::UNIDADES[$u]);
        }
        return trim($out);
    }
}
