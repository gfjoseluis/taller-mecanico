<?php

namespace App\Livewire;

use App\Application\UseCases\GuardarRolUseCase;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleForm extends Component
{
    public $editId = null;
    public string $name = '';
    public array $perms = [];

    public function mount($id = null)
    {
        if ($id) { $r = Role::with('permissions')->findOrFail($id); $this->editId = $r->id; $this->name = $r->name; $this->perms = $r->permissions->pluck('name')->toArray(); }
    }

    public function guardar(GuardarRolUseCase $guardar)
    {
        $this->validate(['name' => 'required|min:3']);
        $guardar->ejecutar($this->editId, $this->name, $this->perms);
        session()->flash("ok", 'Rol guardado.'); $this->redirect('/roles', navigate: true); return;
    }

    public function render() { return view('livewire.role-form', ['todos' => Permission::orderBy('name')->get()]); }
}
