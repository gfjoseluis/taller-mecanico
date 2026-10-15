<?php

namespace App\Livewire;

use App\Application\UseCases\ArchivarUseCase;
use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class ClientTable extends Component
{
    use WithPagination;
    public string $search = '';
    public bool $verEliminados = false;

    public function updatingSearch() { $this->resetPage(); }

    public function eliminar(int $id, ArchivarUseCase $archivar) { $archivar->eliminar(Client::class, $id); }
    public function restaurar(int $id, ArchivarUseCase $archivar) { $archivar->restaurar(Client::class, $id); }

    public function render()
    {
        $q = Client::search($this->search)->orderBy('id', 'desc');
        if ($this->verEliminados) $q = $q->withTrashed();
        return view('livewire.client-table', ['items' => $q->paginate(15)]);
    }
}
