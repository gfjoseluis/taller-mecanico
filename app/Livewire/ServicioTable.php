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
    public bool $soloAntiguas = false;
    public static array $abiertas = ['registrada'];
    public static array $cerradas = ['cerrada', 'cerrada-pagada', 'cerrada-pendiente'];

    protected $queryString = ['search' => ['except' => ''], 'soloAntiguas' => ['except' => false]];

    public function mount() { $this->soloAntiguas = (bool) request('antiguas', false); }

    public function updatingSearch() { $this->resetPage(); }
    public function updatedSoloAntiguas() { $this->resetPage(); }

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
        $s = trim($this->search);
        $q = WorkOrder::with(['client', 'vehicle', 'ultimoPago'])->orderBy('id', 'desc');
        if ($this->soloAntiguas) $q->where('estado', 'registrada')->where('created_at', '<', now()->subDays(7));
        if ($s !== '') {
            $q->where(fn ($w) => $w->where('numero', 'like', "%{$s}%")
                ->orWhereHas('client', fn ($c) => $c->where('nombre', 'like', "%{$s}%"))
                ->orWhereHas('vehicle', fn ($v) => $v->where('placa', 'like', "%{$s}%")));
        }
        return view('livewire.servicio-table', ['items' => $q->paginate(15)]);
    }
}
