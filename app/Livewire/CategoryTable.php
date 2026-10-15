<?php

namespace App\Livewire;

use App\Application\UseCases\ArchivarUseCase;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryTable extends Component
{
    use WithPagination;
    public string $search = '';
    public function eliminar(int $id, ArchivarUseCase $archivar) { $archivar->eliminar(Category::class, $id); }
    public function render() { return view('livewire.category-table', ['items' => Category::search($this->search)->orderBy('id', 'desc')->paginate(15)]); }
}
