<?php

namespace App\Application\Lecturas;

use App\Models\Proforma;
use App\Models\WorkOrder;
use App\Support\GrupoLineas;

/** Detalle de orden para pantalla e impresión (misma fuente, mismo dato). */
class OrdenDetalleService
{
    public function cargar(int $id): WorkOrder
    {
        return WorkOrder::with(['client', 'vehicle', 'lineas.product', 'payments', 'logs', 'recepcion.fotos'])->findOrFail($id);
    }

    /** Servicios parseados del diagnóstico (solo órdenes/pro-formas antiguas, previas a las líneas por tipo). */
    public function serviciosDe(?string $diagnostico): array
    {
        $servicios = [];
        foreach (explode("\n", $diagnostico ?? '') as $ln) {
            if (preg_match('/^- (.*) \(([\d.]+)\)$/', trim($ln), $m)) {
                $servicios[] = ['descripcion' => $m[1], 'precio' => (float) $m[2]];
            }
        }
        return $servicios;
    }

    public function diagnosticoBase(?string $diagnostico): string
    {
        return explode("\n", $diagnostico ?? '')[0] ?? '—';
    }

    /** Líneas agrupadas por tipo (servicio / repuesto / insumo). Única fuente para ver e imprimir. */
    public function gruposDe(WorkOrder|Proforma $m): array
    {
        $lineas = $m->lineas;
        // Órdenes antiguas: los servicios vivían como texto; se leen solo si no hay líneas de servicio.
        if ($lineas->doesntContain(fn ($l) => ($l->tipo ?? null) === 'servicio')) {
            $legado = collect($this->serviciosDe($m->diagnostico))->map(fn ($s) => (object) [
                'tipo' => 'servicio', 'etiqueta' => $s['descripcion'], 'referencia' => '',
                'cantidad' => 1.0, 'precio' => (float) $s['precio'],
            ]);
            $lineas = $lineas->concat($legado);
        }
        return GrupoLineas::agrupar($lineas);
    }
}
