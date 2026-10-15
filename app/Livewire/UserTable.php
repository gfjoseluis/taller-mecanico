<?php

namespace App\Livewire;

use App\Application\UseCases\ArchivarUseCase;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserTable extends Component
{
    use WithPagination;
    public string $search = '';
    public function desactivar(int $id, ArchivarUseCase $archivar) { $archivar->eliminar(User::class, $id); }
    public function restaurar(int $id, ArchivarUseCase $archivar) { $archivar->restaurar(User::class, $id); }
    public function render() { return view('livewire.user-table', ['items' => User::with('roles')->where('name', 'like', "%{$this->search}%")->orderBy('id', 'desc')->paginate(15)]); }
}
