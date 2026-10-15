<?php

namespace App\Livewire;

use App\Application\UseCases\GuardarCategoriaUseCase;
use App\Models\Category;
use Livewire\Component;

class CategoryForm extends Component
{
    public $editId = null;
    public string $nombre = '';
    public string $prefijo = '';
    public string $tipo = 'repuesto';

    public function mount($id = null)
    {
        if ($id) {
            $c = Category::findOrFail($id);
            $this->editId = $c->id; $this->nombre = $c->nombre; $this->prefijo = $c->prefijo; $this->tipo = $c->tipo;
        }
    }

    public function guardar(GuardarCategoriaUseCase $guardar)
    {
        $this->validate(['nombre' => 'required|min:3', 'prefijo' => 'required|max:8', 'tipo' => 'required']);
        $guardar->ejecutar($this->editId, $this->nombre, $this->prefijo, $this->tipo);
        session()->flash("ok", 'Categoría guardada.'); $this->redirect('/categorias', navigate: true); return;
    }

    public function render() { return view('livewire.category-form'); }
}
