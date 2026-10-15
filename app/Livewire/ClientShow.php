<?php

namespace App\Livewire;

use App\Application\UseCases\ArchivarUseCase;
use App\Models\Client;
use App\Models\Vehicle;
use Livewire\Component;

class ClientShow extends Component
{
    public Client $cliente;

    public function mount(int $id)
    {
        $this->cliente = Client::with('vehicles')->findOrFail($id);
    }

    public function eliminarVehiculo(int $id, ArchivarUseCase $archivar)
    {
        $veh = $this->cliente->vehicles()->where('id', $id)->firstOrFail();
        $archivar->eliminar(Vehicle::class, $veh->id);
        $this->cliente->load('vehicles');
    }

    public function render() { return view('livewire.client-show'); }
}
