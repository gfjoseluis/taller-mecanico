<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class RoleTable extends Component
{
    use WithPagination;
    public function render() { return view('livewire.role-table', ['items' => Role::withCount('permissions')->paginate(15)]); }
}
