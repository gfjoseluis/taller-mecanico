<?php

namespace App\Livewire;

use App\Application\UseCases\GuardarCuentaUseCase;
use App\Models\BankAccount;
use Livewire\Component;

class BankForm extends Component
{
    public $editId = null;
    public string $nombre = '';
    public string $banco = '';
    public string $numero = '';
    public string $moneda = 'BOB';
    public float $saldo = 0;

    public function mount($id = null)
    {
        if ($id) { $b = BankAccount::findOrFail($id); $this->editId = $b->id; $this->nombre = $b->nombre; $this->banco = $b->banco; $this->numero = $b->numero; $this->moneda = $b->moneda; $this->saldo = (float) $b->saldo; }
    }

    public function guardar(GuardarCuentaUseCase $guardar)
    {
        $this->validate(['nombre' => 'required', 'banco' => 'required', 'numero' => 'required']);
        $guardar->ejecutar($this->editId, ['nombre' => $this->nombre, 'banco' => $this->banco, 'numero' => $this->numero, 'moneda' => $this->moneda, 'saldo' => $this->saldo]);
        session()->flash("ok", 'Cuenta guardada.'); $this->redirect('/bancos', navigate: true); return;
    }

    public function render() { return view('livewire.bank-form'); }
}
