<?php

namespace App\Support;

use Illuminate\Support\Collection;

/** Agrupa las líneas de una orden o pro-forma por su tipo, sin adivinar por descarte. */
final class GrupoLineas
{
    /** clave canónica => título impreso, en orden de aparición. */
    public const TITULOS = [
        'servicio' => 'Servicios (mano de obra)',
        'repuesto' => 'Repuestos',
        'insumo' => 'Insumos y materiales',
        'otros' => 'Otros',
    ];

    public static function claveDe(?string $tipo): string
    {
        return match ($tipo) {
            'servicio' => 'servicio',
            'repuesto' => 'repuesto',
            'insumo', 'material' => 'insumo',
            default => 'otros',
        };
    }

    /**
     * @param  Collection<int, mixed>  $lineas  objetos con ->tipo, ->etiqueta, ->referencia, ->cantidad, ->precio
     * @return array<int, array{clave:string, titulo:string, lineas:Collection, subtotal:float}>
     */
    public static function agrupar(Collection $lineas): array
    {
        $buckets = [];
        foreach (self::TITULOS as $clave => $titulo) {
            $buckets[$clave] = ['clave' => $clave, 'titulo' => $titulo, 'lineas' => collect(), 'subtotal' => 0.0];
        }
        foreach ($lineas as $l) {
            $clave = self::claveDe($l->tipo ?? null);
            $buckets[$clave]['lineas']->push($l);
            $buckets[$clave]['subtotal'] += (float) ($l->cantidad ?? 0) * (float) ($l->precio ?? 0);
        }
        return array_values(array_filter($buckets, fn ($b) => $b['lineas']->isNotEmpty()));
    }
}
