<?php

namespace App\Livewire;

use App\Application\UseCases\GuardarVehiculoUseCase;
use App\Models\Client;
use App\Models\Vehicle;
use Livewire\Component;

class VehicleForm extends Component
{
    public $editId = null;
    public $client_id = null;
    public string $placa = '';
    public string $marca = '';
    public string $modelo = '';
    public string $chasis = '';
    public ?float $tonelaje = null;

    protected $listeners = ['seleccionado' => 'onSeleccionado'];

    public function onSeleccionado($modelo, $id, $indice = null)
    {
        if ($modelo === 'Client') $this->client_id = $id;
    }

    public function mount($id = null)
    {
        if ($id) { $v = Vehicle::findOrFail($id); $this->editId = $v->id; $this->client_id = $v->client_id; $this->placa = $v->placa; $this->marca = $v->marca ?? ''; $this->modelo = $v->modelo ?? ''; $this->chasis = $v->chasis ?? ''; $this->tonelaje = $v->tonelaje; }
    }

    public function guardar(GuardarVehiculoUseCase $guardar)
    {
        $this->validate(['client_id' => 'required|exists:clients,id', 'placa' => 'required']);
        try {
            $guardar->ejecutar($this->editId, ['client_id' => $this->client_id, 'placa' => $this->placa, 'marca' => $this->marca, 'modelo' => $this->modelo, 'chasis' => $this->chasis, 'tonelaje' => $this->tonelaje]);
        } catch (\DomainException $e) {
            $this->addError('placa', $e->getMessage());
            return;
        }
        session()->flash("ok", 'Vehículo guardado.'); $this->redirect('/vehiculos', navigate: true); return;
    }

    public function render() { return view('livewire.vehicle-form'); }
}
