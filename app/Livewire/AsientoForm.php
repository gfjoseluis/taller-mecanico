<?php

namespace App\Livewire;

use App\Application\Services\AsientoService;
use Livewire\Component;

/** Registro manual del contador (Ej: depreciación). Si no cuadra, no guarda. */
class AsientoForm extends Component
{
    public string $fecha = '';
    public string $glosa = '';
    public array $lineas = [['account_id' => null, 'debe' => 0, 'haber' => 0], ['account_id' => null, 'debe' => 0, 'haber' => 0]];

    protected $listeners = ['seleccionado' => 'onSeleccionado'];

    public function mount() { $this->fecha = now()->toDateString(); }

    public function onSeleccionado($modelo, $id, $indice = null)
    {
        if ($modelo === 'ChartAccount' && $indice !== null) $this->lineas[$indice]['account_id'] = $id;
    }

    public function addLinea() { $this->lineas[] = ['account_id' => null, 'debe' => 0, 'haber' => 0]; }

    public function guardar(AsientoService $asientos)
    {
        $this->validate(['fecha' => 'required|date', 'glosa' => 'required|min:3', 'lineas.*.account_id' => 'required|exists:chart_accounts,id']);
        try {
            $asientos->registrar($this->glosa, 'manual', null, $this->lineas, $this->fecha);
        } catch (\RuntimeException $e) {
            $this->addError('lineas', $e->getMessage());
            return;
        }
        session()->flash('ok', 'Registro guardado (cuadra).');
        $this->redirect('/contabilidad', navigate: true);
    }

    public function render() { return view('livewire.asiento-form'); }
}
