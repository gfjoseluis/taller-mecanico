<?php

namespace App\Livewire;

use App\Application\UseCases\ConciliarMovimientoUseCase;
use App\Models\BankAccount;
use App\Models\BankMovement;
use App\Models\Partner;
use Livewire\Component;
use Livewire\WithPagination;

class MovimientoTable extends Component
{
    use WithPagination;
    public string $search = '';
    public string $filtro = ''; // revisados|pendientes

    public function marcar(int $id, ConciliarMovimientoUseCase $conciliar)
    {
        $conciliar->alternar($id);
    }

    public function render()
    {
        $q = BankMovement::with(['cuenta', 'destino', 'socio'])->orderBy('id', 'desc');
        if ($this->filtro === 'revisados') $q->where('revisado', true);
        if ($this->filtro === 'pendientes') $q->where('revisado', false);
        if ($this->search) $q->where('motivo', 'like', "%{$this->search}%");
        return view('livewire.movimiento-table', ['items' => $q->paginate(15)]);
    }
}
