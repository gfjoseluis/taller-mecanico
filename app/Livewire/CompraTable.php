<?php

namespace App\Livewire;

use App\Application\UseCases\AnularCompraUseCase;
use App\Models\Purchase;
use Livewire\Component;
use Livewire\WithPagination;

class CompraTable extends Component
{
    use WithPagination;
    public string $search = '';

    public function updatingSearch() { $this->resetPage(); }

    public function anular(int $id, AnularCompraUseCase $anular)
    {
        try {
            $anular->ejecutar($id);
        } catch (\DomainException $e) {
            session()->flash('ok', $e->getMessage());
            return;
        }
        session()->flash('ok', 'Compra anulada (stock y banco revertidos).');
    }

    public function render()
    {
        $s = trim($this->search);
        $q = Purchase::with('supplier')->orderBy('id', 'desc');
        if ($s !== '') {
            $q->where(fn ($w) => $w->where('folio', 'like', "%{$s}%")
                ->orWhereHas('supplier', fn ($p) => $p->where('nombre', 'like', "%{$s}%")));
        }
        return view('livewire.compra-table', ['items' => $q->paginate(15)]);
    }
}
