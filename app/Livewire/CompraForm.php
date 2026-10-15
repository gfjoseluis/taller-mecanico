<?php

namespace App\Livewire;

use App\Application\Concerns\ManejaSeleccionados;
use App\Application\UseCases\GuardarProveedorUseCase;
use App\Application\UseCases\RegistrarCompraUseCase;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Livewire\Component;

class CompraForm extends Component
{
    use ManejaSeleccionados;
    public $supplier_id = null;
    public $bank_account_id = null;
    public string $tipo_pago = 'contado';
    public string $glosa = '';
    public array $lineas = [['product_id' => null, 'cantidad' => 1, 'costo' => 0]];
    public bool $modalProveedor = false;
    public string $s_nombre = '';
    public string $s_nit = '';
    public string $s_telefono = '';
    public string $s_direccion = '';
    protected $listeners = ['seleccionado' => 'onSel'];
    public function onSel($modelo, $id, $indice = null)
    {
        if ($modelo === 'Supplier') $this->supplier_id = $id;
        if ($modelo === 'BankAccount') $this->bank_account_id = $id;
        $this->alElegirProducto('lineas', $modelo, $id, $indice, 'costo');
    }
    public function addLinea() { $this->lineas[] = ['product_id' => null, 'cantidad' => 1, 'costo' => 0]; }

    public function guardarProveedor(GuardarProveedorUseCase $guardar)
    {
        $this->validate(['s_nombre' => 'required|min:3']);
        $s = $guardar->ejecutar(null, ['nombre' => $this->s_nombre, 'nit' => $this->s_nit, 'telefono' => $this->s_telefono, 'direccion' => $this->s_direccion]);
        $this->supplier_id = $s->id; $this->modalProveedor = false;
        $this->reset(['s_nombre', 's_nit', 's_telefono', 's_direccion']);
    }
    public function guardar(RegistrarCompraUseCase $registrar)
    {
        $this->validate(['supplier_id' => 'required|exists:suppliers,id']);
        $registrar->ejecutar($this->supplier_id, $this->bank_account_id, $this->tipo_pago, $this->glosa, $this->lineas);
        session()->flash("ok", 'Compra registrada, stock actualizado.'); $this->redirect('/compras', navigate: true); return;
    }
    public function render() { return view('livewire.compra-form'); }
}
