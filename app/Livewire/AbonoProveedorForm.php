<?php

namespace App\Livewire;

use App\Application\Concerns\NormalizaVacios;
use App\Application\UseCases\AbonarCompraUseCase;
use App\Models\Purchase;
use Livewire\Component;

/** Abono a deuda con importadora (compra a crédito). */
class AbonoProveedorForm extends Component
{
    use NormalizaVacios;
    public Purchase $compra;
    public string $medio = 'efectivo';
    public $bank_account_id = null;
    public $monto = 0;

    public function updatedMonto() { $this->vaciosANulo('monto'); }

    protected $listeners = ['seleccionado' => 'onSeleccionado'];

    public function mount(int $id)
    {
        $this->compra = Purchase::with('supplier')->findOrFail($id);
        abort_if($this->compra->estado !== 'vigente' || (float) $this->compra->saldo <= 0, 422, 'Solo compras con deuda.');
        $this->monto = (float) $this->compra->saldo;
    }

    public function onSeleccionado($modelo, $id, $indice = null)
    {
        if ($modelo === 'BankAccount') $this->bank_account_id = $id;
    }

    public function guardar(AbonarCompraUseCase $abonar)
    {
        $this->validate([
            'medio' => 'required|in:efectivo,transferencia',
            'bank_account_id' => 'required_if:medio,transferencia|nullable|exists:bank_accounts,id',
            'monto' => 'required|numeric|min:0.01|max:'.$this->compra->saldo,
        ]);
        try {
            $abonar->ejecutar($this->compra->id, $this->medio, $this->bank_account_id, $this->monto);
        } catch (\DomainException $e) {
            $this->addError('monto', $e->getMessage());
            return;
        }
        session()->flash('ok', 'Abono a importadora registrado.');
        $this->redirect('/por-pagar', navigate: true);
    }

    public function render() { return view('livewire.abono-proveedor-form'); }
}
