<?php

namespace App\Livewire;

use App\Application\UseCases\AnularOrdenUseCase;
use App\Models\WorkOrder;
use Livewire\Component;
use Livewire\WithPagination;

class ServicioTable extends Component
{
    use WithPagination;
    public string $search = '';
    public static array $abiertas = ['registrada'];
    public static array $cerradas = ['cerrada', 'cerrada-pagada', 'cerrada-pendiente'];

    public function anular(int $id, AnularOrdenUseCase $anular)
    {
        try {
            $anular->ejecutar($id);
        } catch (\DomainException $e) {
            session()->flash('ok', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.servicio-table', [
            'items' => WorkOrder::with(['client', 'vehicle', 'parent', 'hijas', 'ultimoPago'])->orderBy('id', 'desc')->paginate(15),
        ]);
    }
}
