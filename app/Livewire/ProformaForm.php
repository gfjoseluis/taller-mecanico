<?php

namespace App\Livewire;

use App\Application\Concerns\ManejaSeleccionados;
use App\Application\Concerns\NormalizaVacios;
use App\Application\UseCases\GuardarProformaUseCase;
use App\Models\Client;
use App\Models\Product;
use App\Models\Proforma;
use App\Models\Vehicle;
use Livewire\Component;

/** Pro-forma: presupuesto para el cliente (precios sí, stock y dinero no). */
class ProformaForm extends Component
{
    use ManejaSeleccionados;
    use NormalizaVacios;
    public $editId = null;
    public $client_id = null;
    public $vehicle_id = null;
    public string $diagnostico = '';
    public $descuento = null;
    public array $servicios = [];
    public array $lineas = [];

    protected $listeners = ['seleccionado' => 'onSeleccionado'];

    public function mount($id = null)
    {
        if ($id) {
            $p = Proforma::with('lineas')->findOrFail($id);
            abort_if($p->estado !== 'pendiente', 422, 'Solo pendientes se editan.');
            $this->editId = $p->id;
            $this->client_id = $p->client_id; $this->vehicle_id = $p->vehicle_id;
            $this->diagnostico = explode("\n", $p->diagnostico ?? '')[0] ?? '';
            $this->descuento = (float) $p->descuento;
            $this->servicios = $p->lineas->where('tipo', 'servicio')->map(fn ($l) => ['descripcion' => (string) $l->descripcion, 'precio' => (float) $l->precio])->values()->toArray();
            if (empty($this->servicios)) {
                foreach (explode("\n", $p->diagnostico ?? '') as $ln) {
                    if (preg_match('/^- (.*) \(([\d.]+)\)$/', trim($ln), $m)) $this->servicios[] = ['descripcion' => $m[1], 'precio' => (float) $m[2]];
                }
            }
            $this->lineas = $p->lineas->whereNotNull('product_id')->map(fn ($l) => ['product_id' => $l->product_id, 'cantidad' => (float) $l->cantidad, 'precio' => (float) $l->precio])->values()->toArray();
        }
    }

    public function onSeleccionado($modelo, $id, $indice = null)
    {
        if ($modelo === 'Client') {
            $this->client_id = $id;
            $this->vehicle_id = null;
        }
        $this->alElegirProducto('lineas', $modelo, $id, $indice, 'precio');
    }

    public function vehiculosDelCliente()
    {
        if (! $this->client_id) return collect();
        return Vehicle::where('client_id', $this->client_id)->orderBy('placa')->get();
    }

    public function addLinea() { $this->lineas[] = ['product_id' => null, 'cantidad' => 1, 'precio' => 0]; }
    public function addServicio() { $this->servicios[] = ['descripcion' => '', 'precio' => 0]; }
    public function quitarServicio(int $i) { unset($this->servicios[$i]); $this->servicios = array_values($this->servicios); }

    public function updatedDescuento() { $this->vaciosANulo('descuento'); }

    public function getTotalServiciosProperty(): float
    {
        return (float) collect($this->servicios)->sum(fn ($s) => (float) ($s['precio'] ?? 0));
    }

    public function getTotalInsumosProperty(): float
    {
        return (float) collect($this->lineas)->sum(fn ($l) => (float) ($l['cantidad'] ?? 0) * (float) ($l['precio'] ?? 0));
    }

    public function getSubtotalProperty(): float
    {
        return $this->totalServicios + $this->totalInsumos;
    }

    public function getTotalProperty(): float
    {
        return max(0, $this->subtotal - (float) $this->descuento);
    }

    public function guardar(GuardarProformaUseCase $guardar)
    {
        $this->vaciosANulo('descuento');
        $this->servicios = collect($this->servicios)->map(fn ($s) => ['descripcion' => $s['descripcion'] ?? null, 'precio' => ($s['precio'] ?? null) === '' ? null : ($s['precio'] ?? null)])->toArray();
        $this->lineas = collect($this->lineas)->map(fn ($l) => ['product_id' => $l['product_id'] ?? null, 'cantidad' => ($l['cantidad'] ?? null) === '' ? null : ($l['cantidad'] ?? null), 'precio' => ($l['precio'] ?? null) === '' ? null : ($l['precio'] ?? null)])->toArray();
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'servicios.*.precio' => 'nullable|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
        ]);
        try {
            $guardar->ejecutar($this->editId, $this->client_id, $this->vehicle_id, $this->diagnostico, 0.0, $this->servicios, $this->lineas, (float) $this->descuento);
        } catch (\DomainException $e) {
            $this->addError(str_contains($e->getMessage(), 'Descuento') ? 'descuento' : 'vehicle_id', $e->getMessage());
            return;
        }
        session()->flash('ok', 'Pro-forma guardada (sin mover stock).');
        $this->redirect('/proformas', navigate: true);
    }

    public function render()
    {
        $ids = collect($this->lineas)->map(fn ($l) => $l['product_id'] ?? null)->filter()->values();
        $prods = $ids->isEmpty() ? collect() : Product::whereIn('id', $ids)->get();
        return view('livewire.proforma-form', [
            'vehiculosCliente' => $this->vehiculosDelCliente(),
            'ventas' => $prods->pluck('precio_venta', 'id')->toArray(),
        ]);
    }
}
