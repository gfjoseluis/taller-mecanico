<?php

namespace App\Livewire;

use App\Application\UseCases\ArchivarUseCase;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierTable extends Component
{
    use WithPagination;
    public string $search = '';
    public function eliminar(int $id, ArchivarUseCase $archivar) { $archivar->eliminar(Supplier::class, $id); }
    public function render() { return view('livewire.supplier-table', ['items' => Supplier::search($this->search)->orderBy('id', 'desc')->paginate(15)]); }
}
