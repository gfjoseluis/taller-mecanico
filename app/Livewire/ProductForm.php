<?php

namespace App\Livewire;

use App\Application\Concerns\NormalizaVacios;
use App\Application\UseCases\GuardarProductoUseCase;
use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

class ProductForm extends Component
{
    use NormalizaVacios;
    public $editId = null;
    public string $nombre = '';
    public $category_id = null;
    public $precio_compra = null;
    public $precio_venta = null;
    public float $stock = 0;
    public $stock_minimo = null;

    public function updatedPrecioCompra() { $this->vaciosANulo('precio_compra'); }
    public function updatedPrecioVenta() { $this->vaciosANulo('precio_venta'); }
    public function updatedStockMinimo() { $this->vaciosANulo('stock_minimo'); }

    protected $listeners = ['seleccionado' => 'onSeleccionado'];

    public function onSeleccionado($modelo, $id, $indice = null)
    {
        if ($modelo === 'Category') $this->category_id = $id;
    }

    public function mount($id = null)
    {
        if ($id) { $p = Product::findOrFail($id); $this->editId = $p->id; $this->nombre = $p->nombre; $this->category_id = $p->category_id; $this->precio_compra = (float) $p->precio_compra; $this->precio_venta = (float) $p->precio_venta; $this->stock = (float) $p->stock; $this->stock_minimo = (float) $p->stock_minimo; }
    }

    public function guardar(GuardarProductoUseCase $guardar)
    {
        $this->vaciosANulo('precio_compra', 'precio_venta', 'stock_minimo');
        $this->validate(['nombre' => 'required|min:3', 'category_id' => 'required|exists:categories,id', 'precio_venta' => 'required|numeric|min:0']);
        $this->precio_compra ??= 0;
        $this->stock_minimo ??= 0;
        try {
            $guardar->ejecutar($this->editId, ['nombre' => $this->nombre, 'category_id' => $this->category_id, 'precio_compra' => $this->precio_compra, 'precio_venta' => $this->precio_venta, 'stock_minimo' => $this->stock_minimo]);
        } catch (\DomainException $e) {
            $this->addError('category_id', $e->getMessage());
            return;
        }
        session()->flash("ok", 'Producto guardado con SKU autogenerado.'); $this->redirect('/productos', navigate: true); return;
    }

    public function render() { return view('livewire.product-form'); }
}
