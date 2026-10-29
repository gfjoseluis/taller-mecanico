<?php

namespace App\Livewire;

use App\Application\Lecturas\OrdenDetalleService;
use App\Models\WorkOrder;
use Livewire\Component;

/** Vista de solo lectura de la orden: cliente, vehículo, diagnóstico, servicios, repuestos, cobros. */
class ServicioShow extends Component
{
    public WorkOrder $orden;
    public array $grupos = [];
    public string $diagnosticoBase = '';

    public function mount(int $id, OrdenDetalleService $detalle)
    {
        $this->orden = $detalle->cargar($id);
        $this->grupos = $detalle->gruposDe($this->orden);
        $this->diagnosticoBase = $detalle->diagnosticoBase($this->orden->diagnostico);
    }

    public function render()
    {
        return view('livewire.servicio-show');
    }
}
