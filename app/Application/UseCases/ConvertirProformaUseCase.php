<?php

namespace App\Application\UseCases;

use App\Models\Proforma;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

/** Aceptar pro-forma: nace la OT con sus líneas (ahí sí descuenta stock). */
class ConvertirProformaUseCase
{
    public function __construct(private CrearOrdenUseCase $crear) {}

    public function ejecutar(int $proformaId): WorkOrder
    {
        return DB::transaction(function () use ($proformaId) {
            $p = Proforma::with('lineas')->findOrFail($proformaId);
            if ($p->estado !== 'pendiente') throw new \DomainException('Solo pendientes se convierten.');
            if ($p->orden) throw new \DomainException('Ya tiene orden: '.$p->orden->numero.'.');
            $servicios = $p->lineas->where('tipo', 'servicio')
                ->map(fn ($l) => ['descripcion' => (string) $l->descripcion, 'precio' => (float) $l->precio])->values()->toArray();
            $base = explode("\n", $p->diagnostico ?? '')[0] ?? '';
            $lineas = $p->lineas->whereNotNull('product_id')
                ->map(fn ($l) => ['product_id' => $l->product_id, 'cantidad' => (float) $l->cantidad, 'precio' => (float) $l->precio])->toArray();
            $ot = $this->crear->ejecutar($p->client_id, $p->vehicle_id, $base, 0.0, $servicios, $lineas, null, (float) $p->descuento, $p->id);
            $p->update(['estado' => 'aceptada']);
            return $ot;
        });
    }
}
