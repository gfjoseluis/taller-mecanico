<?php

namespace App\Livewire;

use App\Application\Lecturas\OrdenDetalleService;
use App\Models\Proforma;
use Livewire\Component;

/** Ficha de pro-forma con sus precios (sin stock ni dinero). */
class ProformaShow extends Component
{
    public Proforma $proforma;
    public array $grupos = [];
    public string $diagnosticoBase = '';

    public function mount(int $id, OrdenDetalleService $detalle)
    {
        $this->proforma = Proforma::with(['client', 'vehicle', 'lineas.product', 'orden'])->findOrFail($id);
        $this->grupos = $detalle->gruposDe($this->proforma);
        $this->diagnosticoBase = $detalle->diagnosticoBase($this->proforma->diagnostico);
    }

    public function render()
    {
        return view('livewire.proforma-show');
    }
}
