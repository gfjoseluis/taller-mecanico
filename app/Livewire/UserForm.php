<?php

namespace App\Livewire;

use App\Application\UseCases\GuardarUsuarioUseCase;
use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserForm extends Component
{
    public $editId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public array $roles = [];

    public function mount($id = null)
    {
        if ($id) { $u = User::with('roles')->findOrFail($id); $this->editId = $u->id; $this->name = $u->name; $this->email = $u->email; $this->roles = $u->roles->pluck('name')->toArray(); }
    }

    public function guardar(GuardarUsuarioUseCase $guardar)
    {
        $this->validate(['name' => 'required|min:3', 'email' => 'required|email']);
        $data = ['name' => $this->name, 'email' => $this->email];
        if ($this->password) $data['password'] = bcrypt($this->password);
        elseif (! $this->editId) { $this->addError('password', 'Contraseña requerida. Ej: Taller123*'); return; }
        $guardar->ejecutar($this->editId, $data, $this->roles);
        session()->flash("ok", 'Usuario guardado.'); $this->redirect('/usuarios', navigate: true); return;
    }

    public function render() { return view('livewire.user-form', ['todosRoles' => Role::all()]); }
}
