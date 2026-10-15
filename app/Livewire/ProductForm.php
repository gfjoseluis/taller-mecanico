<?php

namespace App\Livewire;

use App\Application\UseCases\GuardarProductoUseCase;
use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

class ProductForm extends Component
{
    public $editId = null;
    public string $nombre = '';
    public string $tipo = 'repuesto';
    public $category_id = null;
    public float $precio_compra = 0;
    public float $precio_venta = 0;
    public float $stock = 0;
    public float $stock_minimo = 0;

    protected $listeners = ['seleccionado' => 'onSeleccionado'];

    public function onSeleccionado($modelo, $id, $indice = null)
    {
        if ($modelo === 'Category') $this->category_id = $id;
    }

    public function mount($id = null)
    {
        if ($id) { $p = Product::findOrFail($id); $this->editId = $p->id; $this->nombre = $p->nombre; $this->tipo = $p->tipo; $this->category_id = $p->category_id; $this->precio_compra = (float) $p->precio_compra; $this->precio_venta = (float) $p->precio_venta; $this->stock = (float) $p->stock; $this->stock_minimo = (float) $p->stock_minimo; }
    }

    public function guardar(GuardarProductoUseCase $guardar)
    {
        $this->validate(['nombre' => 'required|min:3', 'tipo' => 'required', 'precio_venta' => 'required|numeric|min:0']);
        $guardar->ejecutar($this->editId, ['nombre' => $this->nombre, 'tipo' => $this->tipo, 'category_id' => $this->category_id, 'precio_compra' => $this->precio_compra, 'precio_venta' => $this->precio_venta, 'stock' => $this->stock, 'stock_minimo' => $this->stock_minimo]);
        session()->flash("ok", 'Producto guardado con SKU autogenerado.'); $this->redirect('/productos', navigate: true); return;
    }

    public function render() { return view('livewire.product-form'); }
}
