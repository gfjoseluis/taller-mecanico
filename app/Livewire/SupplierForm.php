<?php

namespace App\Livewire;

use App\Application\UseCases\GuardarProveedorUseCase;
use App\Models\Supplier;
use Livewire\Component;

class SupplierForm extends Component
{
    public $editId = null;
    public string $nombre = '';
    public string $nit = '';
    public string $telefono = '';
    public string $direccion = '';

    public function mount($id = null)
    {
        if ($id) { $s = Supplier::findOrFail($id); $this->editId = $s->id; $this->nombre = $s->nombre; $this->nit = $s->nit ?? ''; $this->telefono = $s->telefono ?? ''; $this->direccion = $s->direccion ?? ''; }
    }

    public function guardar(GuardarProveedorUseCase $guardar)
    {
        $this->validate(['nombre' => 'required|min:3']);
        $guardar->ejecutar($this->editId, ['nombre' => $this->nombre, 'nit' => $this->nit, 'telefono' => $this->telefono, 'direccion' => $this->direccion]);
        session()->flash("ok", 'Proveedor guardado.'); $this->redirect('/proveedores', navigate: true); return;
    }

    public function render() { return view('livewire.supplier-form'); }
}
