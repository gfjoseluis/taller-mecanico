<?php

namespace App\Livewire;

use App\Application\UseCases\AnularCompraUseCase;
use App\Models\Purchase;
use Livewire\Component;
use Livewire\WithPagination;

class CompraTable extends Component
{
    use WithPagination;
    public function anular(int $id, AnularCompraUseCase $anular)
    {
        $anular->ejecutar($id);
        session()->flash('ok', 'Compra anulada (stock y banco revertidos).');
    }
    public function render() { return view('livewire.compra-table', ['items' => Purchase::with('supplier')->orderBy('id', 'desc')->paginate(15)]); }
}
