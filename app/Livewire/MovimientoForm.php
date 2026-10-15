<?php

namespace App\Livewire;

use App\Application\UseCases\RegistrarMovimientoUseCase;
use App\Models\Partner;
use Livewire\Component;

class MovimientoForm extends Component
{
    public string $tipo = 'salida'; // salida(gasto)|entrada|traspaso|aporte|retiro
    public $cuenta_id = null;
    public $cuenta_destino_id = null;
    public $partner_id = null;
    public $contra_id = null;
    public float $monto = 0;
    public string $motivo = '';

    protected $listeners = ['seleccionado' => 'onSeleccionado'];

    public function onSeleccionado($modelo, $id, $indice = null)
    {
        if ($modelo === 'BankAccount' && $indice === 'origen') $this->cuenta_id = $id;
        if ($modelo === 'BankAccount' && $indice === 'destino') $this->cuenta_destino_id = $id;
        if ($modelo === 'Partner') $this->partner_id = $id;
        if ($modelo === 'ChartAccount') $this->contra_id = $id;
    }

    public function guardar(RegistrarMovimientoUseCase $registrar)
    {
        $this->validate([
            'tipo' => 'required|in:salida,entrada,traspaso,aporte,retiro',
            'cuenta_id' => 'required|exists:bank_accounts,id',
            'monto' => 'required|numeric|min:0.01',
            'motivo' => 'required|min:3',
        ]);
        if ($this->tipo === 'traspaso') {
            $this->validate(['cuenta_destino_id' => 'required|exists:bank_accounts,id|different:cuenta_id']);
        }
        if (in_array($this->tipo, ['aporte', 'retiro'])) {
            $this->validate(['partner_id' => 'required|exists:partners,id']);
        }
        if (in_array($this->tipo, ['salida', 'entrada'])) {
            $this->validate(['contra_id' => 'required|exists:chart_accounts,id']);
        }
        $registrar->ejecutar($this->tipo, $this->cuenta_id, $this->cuenta_destino_id, $this->partner_id, $this->contra_id, $this->monto, $this->motivo);
        session()->flash('ok', 'Movimiento registrado y saldo actualizado.');
        $this->redirect('/movimientos', navigate: true);
    }

    public function render() { return view('livewire.movimiento-form', ['socios' => Partner::orderBy('nombre')->get()]); }
}
