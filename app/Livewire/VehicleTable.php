<?php

namespace App\Livewire;

use App\Application\UseCases\ArchivarUseCase;
use App\Models\Vehicle;
use Livewire\Component;
use Livewire\WithPagination;

class VehicleTable extends Component
{
    use WithPagination;
    public string $search = '';

    public function eliminar(int $id, ArchivarUseCase $archivar) { $archivar->eliminar(Vehicle::class, $id); }

    public function render()
    {
        return view('livewire.vehicle-table', [
            'items' => Vehicle::with('client')->search($this->search)->orderBy('id', 'desc')->paginate(15),
        ]);
    }
}
