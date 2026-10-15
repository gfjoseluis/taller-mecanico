<?php

namespace App\Livewire;

use App\Application\UseCases\CobrarOrdenUseCase;
use App\Models\WorkOrder;
use Livewire\Component;

/** Paso 4 cajero: valida y delega el cobro (medios abiertos a extensión). */
class CobroForm extends Component
{
    public WorkOrder $orden;
    public string $medio = 'efectivo'; // efectivo|transferencia|crédito
    public $bank_account_id = null;
    public float $recibido = 0;

    protected $listeners = ['seleccionado' => 'onSeleccionado'];

    public function mount(int $id)
    {
        $this->orden = WorkOrder::with(['client', 'vehicle'])->findOrFail($id);
        abort_if(in_array($this->orden->estado, ['cerrada', 'cerrada-pagada', 'cerrada-pendiente', 'anulada']), 422, 'Orden ya cerrada o anulada.');
        $this->recibido = (float) $this->orden->total;
    }

    public function onSeleccionado($modelo, $id, $indice = null)
    {
        if ($modelo === 'BankAccount') $this->bank_account_id = $id;
    }

    public function getVueltoProperty(): float
    {
        return max(0, $this->recibido - (float) $this->orden->total);
    }

    public function getFaltaProperty(): float
    {
        return max(0, (float) $this->orden->total - $this->recibido);
    }

    public function guardar(CobrarOrdenUseCase $cobrar)
    {
        $this->validate([
            'medio' => 'required|in:efectivo,transferencia,credito',
            'bank_account_id' => 'required_if:medio,transferencia|nullable|exists:bank_accounts,id',
            'recibido' => 'required|numeric|min:0',
        ]);
        try {
            $cobrar->ejecutar($this->orden->id, $this->medio, $this->bank_account_id, $this->recibido);
        } catch (\DomainException $e) {
            $this->addError('recibido', $e->getMessage());
            return;
        }
        session()->flash('ok', 'Cobro registrado y orden cerrada.');
        $this->redirect('/servicios', navigate: true);
    }

    public function render() { return view('livewire.cobro-form'); }
}
