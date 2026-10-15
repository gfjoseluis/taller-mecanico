<?php

namespace App\Livewire;

use App\Application\UseCases\ArchivarUseCase;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductTable extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $verEliminados = false;

    public function updatingSearch() { $this->resetPage(); }

    public function eliminar(int $id, ArchivarUseCase $archivar) { $archivar->eliminar(Product::class, $id); }
    public function restaurar(int $id, ArchivarUseCase $archivar) { $archivar->restaurar(Product::class, $id); }

    public function render()
    {
        $q = Product::with('category')->search($this->search)->orderBy('id', 'desc');
        if ($this->verEliminados) $q = $q->withTrashed();
        return view('livewire.product-table', ['items' => $q->paginate(config('taller.paginacion', 15))]);
    }
}
