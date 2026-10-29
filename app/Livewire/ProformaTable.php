<?php

namespace App\Livewire;

use App\Models\Proforma;
use Livewire\Component;
use Livewire\WithPagination;

class ProformaTable extends Component
{
    use WithPagination;
    public string $search = '';

    protected $queryString = ['search' => ['except' => '']];

    public function updatingSearch() { $this->resetPage(); }

    public function render()
    {
        $s = trim($this->search);
        $q = Proforma::with(['client', 'vehicle'])->orderBy('id', 'desc');
        if ($s !== '') {
            $q->where(fn ($w) => $w->where('numero', 'like', "%{$s}%")
                ->orWhereHas('client', fn ($c) => $c->where('nombre', 'like', "%{$s}%"))
                ->orWhereHas('vehicle', fn ($v) => $v->where('placa', 'like', "%{$s}%")));
        }
        return view('livewire.proforma-table', ['items' => $q->paginate(15)]);
    }
}
