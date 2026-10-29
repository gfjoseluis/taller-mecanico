<?php

namespace App\Livewire;

use App\Application\Concerns\NormalizaVacios;
use App\Application\UseCases\AbonarOrdenUseCase;
use App\Models\WorkOrder;
use Livewire\Component;

/** Abono a deuda del cliente (fiado/credito posterior). */
class AbonoForm extends Component
{
    use NormalizaVacios;
    public WorkOrder $orden;
    public string $medio = 'efectivo';
    public $bank_account_id = null;
    public $monto = 0;

    public function updatedMonto() { $this->vaciosANulo('monto'); }

    protected $listeners = ['seleccionado' => 'onSeleccionado'];

    public function mount(int $id)
    {
        $this->orden = WorkOrder::with('payments')->findOrFail($id);
        abort_if($this->orden->estado !== 'cerrada-pendiente', 422, 'Solo órdenes con deuda.');
        $this->monto = $this->pendiente;
    }

    public function onSeleccionado($modelo, $id, $indice = null)
    {
        if ($modelo === 'BankAccount') $this->bank_account_id = $id;
    }

    public function getPendienteProperty(): float
    {
        return max(0, (float) $this->orden->total - (float) $this->orden->payments()->sum('recibido'));
    }

    public function guardar(AbonarOrdenUseCase $abonar)
    {
        $this->validate([
            'medio' => 'required|in:efectivo,transferencia',
            'bank_account_id' => 'required_if:medio,transferencia|nullable|exists:bank_accounts,id',
            'monto' => 'required|numeric|min:0.01|max:'.$this->pendiente,
        ]);
        try {
            $abonar->ejecutar($this->orden->id, $this->medio, $this->bank_account_id, $this->monto);
        } catch (\DomainException $e) {
            $this->addError('monto', $e->getMessage());
            return;
        }
        session()->flash('ok', 'Abono registrado.');
        $this->redirect('/por-cobrar', navigate: true);
    }

    public function render() { return view('livewire.abono-form'); }
}
