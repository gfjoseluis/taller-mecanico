<?php

namespace App\Livewire;

use App\Application\Concerns\NormalizaVacios;
use App\Application\UseCases\AnotarAdelantoUseCase;
use App\Application\UseCases\CobrarOrdenUseCase;
use App\Models\WorkOrder;
use Livewire\Component;

/** Pantalla única de cobro: recibe todo (cierra), recibe menos (adelanto, sigue abierta) o queda debiendo. */
class CobroForm extends Component
{
    use NormalizaVacios;
    public WorkOrder $orden;
    public string $medio = 'efectivo'; // efectivo|transferencia
    public $bank_account_id = null;
    public $recibido = 0;

    protected $listeners = ['seleccionado' => 'onSeleccionado'];

    public function mount(int $id, AnotarAdelantoUseCase $adelantos)
    {
        $this->orden = WorkOrder::with(['client', 'vehicle'])->findOrFail($id);
        abort_if(in_array($this->orden->estado, ['cerrada', 'cerrada-pagada', 'cerrada-pendiente', 'anulada']), 422, 'Orden ya cerrada o anulada.');
        $this->recibido = $adelantos->falta($this->orden);
    }

    public function onSeleccionado($modelo, $id, $indice = null)
    {
        if ($modelo === 'BankAccount') $this->bank_account_id = $id;
    }

    public function getYaDioProperty(): float
    {
        return (float) $this->orden->payments()->sum('recibido');
    }

    public function getFaltaProperty(): float
    {
        return max(0, (float) $this->orden->total - $this->yaDio);
    }

    public function getVueltoProperty(): float
    {
        return max(0, (float) $this->recibido - $this->falta);
    }

    /** Un solo botón: alcanza o sobra y cierra, falta y queda como adelanto. */
    public function guardar(CobrarOrdenUseCase $cobrar, AnotarAdelantoUseCase $adelantos)
    {
        $this->vaciosANulo('recibido');
        if ($this->recibido === null) $this->recibido = 0;
        $this->validate([
            'medio' => 'required|in:efectivo,transferencia',
            'bank_account_id' => 'required_if:medio,transferencia|nullable|exists:bank_accounts,id',
            'recibido' => 'required|numeric|min:0',
        ]);
        if ($this->recibido <= 0 && $this->falta > 0) {
            $this->addError('recibido', 'Indica cuánto recibe ahora o usa Queda debiendo.');
            return;
        }
        try {
            if ($this->recibido < $this->falta) {
                $adelantos->ejecutar($this->orden->id, $this->medio, $this->bank_account_id, $this->recibido);
                session()->flash('ok', 'Adelanto registrado. Faltan '.number_format($this->falta - $this->recibido, 2).' Bs, la orden sigue abierta.');
            } else {
                $cobrar->ejecutar($this->orden->id, $this->medio, $this->bank_account_id, $this->recibido);
                session()->flash('ok', 'Cobro registrado y orden cerrada.');
            }
        } catch (\DomainException $e) {
            $this->addError('recibido', $e->getMessage());
            return;
        }
        $this->redirect('/servicios', navigate: true);
    }

    /** Cierra sin cobrar ahora: se lo llevó debiendo lo que falta. */
    public function quedaDebiendo(CobrarOrdenUseCase $cobrar)
    {
        try {
            $cobrar->ejecutar($this->orden->id, 'credito', null, 0);
        } catch (\DomainException $e) {
            $this->addError('recibido', $e->getMessage());
            return;
        }
        session()->flash('ok', 'Queda debiendo '.number_format($this->falta, 2).' Bs.');
        $this->redirect('/por-cobrar', navigate: true);
    }

    public function render() { return view('livewire.cobro-form'); }
}
