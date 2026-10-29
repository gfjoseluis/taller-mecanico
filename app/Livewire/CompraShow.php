<?php

namespace App\Livewire;

use App\Application\Lecturas\CompraDetalleService;
use App\Models\Purchase;
use Livewire\Component;

/** Vista de solo lectura de la compra: proveedor, líneas, total, saldo, abonos. */
class CompraShow extends Component
{
    public Purchase $compra;

    public function mount(int $id, CompraDetalleService $detalle)
    {
        $this->compra = $detalle->cargar($id);
    }

    public function render()
    {
        return view('livewire.compra-show');
    }
}
