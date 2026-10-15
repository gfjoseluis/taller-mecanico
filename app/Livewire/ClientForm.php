<?php

namespace App\Livewire;

use App\Application\UseCases\GuardarClienteUseCase;
use App\Models\Client;
use Livewire\Component;

class ClientForm extends Component
{
    public $editId = null;
    public string $nombre = '';
    public string $nit_ci = '';
    public string $telefono = '';
    public string $email = '';
    public string $direccion = '';
    /** Detalle add-vehicle: filas en el mismo registro. */
    public array $vehiculos = [];

    public function mount($id = null)
    {
        if ($id) {
            $c = Client::with('vehicles')->findOrFail($id);
            $this->editId = $c->id; $this->nombre = $c->nombre; $this->nit_ci = $c->nit_ci;
            $this->telefono = $c->telefono ?? ''; $this->email = $c->email ?? ''; $this->direccion = $c->direccion ?? '';
            $this->vehiculos = $c->vehicles->map(fn ($v) => ['id' => $v->id, 'placa' => $v->placa, 'marca' => $v->marca ?? '', 'modelo' => $v->modelo ?? '', 'chasis' => $v->chasis ?? '', 'tonelaje' => $v->tonelaje])->toArray();
        }
    }

    public function addVehiculo() { $this->vehiculos[] = ['id' => null, 'placa' => '', 'marca' => '', 'modelo' => '', 'chasis' => '', 'tonelaje' => null]; }
    public function quitarVehiculo(int $i) { unset($this->vehiculos[$i]); $this->vehiculos = array_values($this->vehiculos); }

    public function guardar(GuardarClienteUseCase $guardar)
    {
        $this->validate([
            'nombre' => 'required|min:3', 'nit_ci' => 'required',
            'vehiculos.*.placa' => 'nullable',
        ]);
        try {
            $guardar->ejecutar($this->editId, [
                'nombre' => $this->nombre, 'nit_ci' => $this->nit_ci, 'telefono' => $this->telefono,
                'email' => $this->email, 'direccion' => $this->direccion,
            ], $this->vehiculos);
        } catch (\DomainException $e) {
            $this->addError('vehiculos', $e->getMessage());
            return;
        }
        session()->flash("ok", 'Cliente guardado con sus vehículos.'); $this->redirect('/clientes', navigate: true); return;
    }

    public function render() { return view('livewire.client-form'); }
}
