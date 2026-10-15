<?php

namespace App\Livewire;

use Livewire\Component;

/** Select con buscador integrado server-side para listas largas (scroll imposible). */
class SearchableSelect extends Component
{
    public string $modelo = 'Client'; // Client|Vehicle|Product|Supplier|BankAccount|Category
    public $value = null;
    public string $label = '';
    public string $query = '';
    public string $placeholder = 'Buscar... Ej: nombre, placa, SKU';
    public $filtro = null; // ej client_id para vehículos
    public $indice = null; // fila en líneas dinámicas (servicio/compra)
    public array $resultados = [];

    public function mount(string $modelo = 'Client', $value = null, string $label = '', ?string $placeholder = null, $filtro = null, $indice = null)
    {
        $this->modelo = $modelo;
        $this->value = $value;
        $this->label = $label;
        if ($placeholder) $this->placeholder = $placeholder;
        $this->filtro = $filtro;
        $this->indice = $indice;
        $this->cargarLabel();
    }

    public function updatedQuery() { $this->buscar(); }
    public function updatedFiltro() { $this->buscar(); }

    public function buscar()
    {
        $clase = '\\App\\Models\\'.$this->modelo;
        if (! class_exists($clase)) { $this->resultados = []; return; }
        $q = $clase::query();
        if ($this->modelo === 'Vehicle' && $this->filtro) $q->where('client_id', $this->filtro);
        $s = trim($this->query);
        if (strlen($s) >= 2) {
            if (method_exists($clase, 'scopeSearch')) $q->search($s);
            else $q->where('nombre', 'like', "%{$s}%");
        } else {
            $q->orderBy('id', 'desc');
        }
        $this->resultados = $q->limit(20)->get()->map(fn ($r) => ['id' => $r->id, 'texto' => $this->texto($r)])->toArray();
    }

    private function texto($r): string
    {
        return match ($this->modelo) {
            'Product' => ($r->sku ?? '').' — '.($r->nombre ?? '').' (stock: '.$r->stock.' · venta: '.number_format($r->precio_venta, 2).')',
            'BankAccount' => ($r->nombre ?? '').' — '.($r->numero ?? ''),
            default => $r->nombre ?? (($r->placa ?? $r->sku ?? ('#'.$r->id)).' '.($r->marca ?? '')),
        };
    }

    public function elegir($id, $texto)
    {
        $this->value = $id;
        $this->label = $texto;
        $this->query = '';
        $this->resultados = [];
        $this->dispatch('seleccionado', modelo: $this->modelo, id: $id, indice: $this->indice);
    }

    public function limpiar()
    {
        $this->value = null; $this->label = '';
        $this->dispatch('seleccionado', modelo: $this->modelo, id: null, indice: $this->indice);
    }

    private function cargarLabel()
    {
        if (! $this->value) return;
        $clase = '\\App\\Models\\'.$this->modelo;
        $r = $clase::find($this->value);
        if ($r) $this->label = $this->texto($r);
    }

    public function render() { return view('livewire.searchable-select'); }
}
