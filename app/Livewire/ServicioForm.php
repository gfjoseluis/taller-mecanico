<?php

namespace App\Livewire;

use App\Application\Concerns\ManejaSeleccionados;
use App\Application\UseCases\CrearOrdenUseCase;
use App\Application\UseCases\EditarOrdenUseCase;
use App\Application\UseCases\GuardarClienteUseCase;
use App\Application\UseCases\GuardarVehiculoUseCase;
use App\Models\Client;
use App\Models\Product;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Livewire\Component;

/** Wizard cajero: valida y delega; la lógica vive en casos de uso. */
class ServicioForm extends Component
{
    use ManejaSeleccionados;
    public $editId = null;
    public $parent_id = null;
    public $client_id = null;
    public $vehicle_id = null;
    public string $diagnostico = '';
    public float $mano_obra = 0;
    /** Servicios a realizar (mano de obra por ítem). */
    public array $servicios = [];
    public bool $usarInsumos = false;
    public array $lineas = [];
    public bool $modalCliente = false;
    public bool $showAddVehiculo = false;
    public string $c_nombre = '';
    public string $c_nit_ci = '';
    public string $c_telefono = '';
    public string $c_email = '';
    public string $c_direccion = '';
    public string $v_placa = '';
    public string $v_marca = '';
    public string $v_modelo = '';
    public string $v_chasis = '';
    public $v_tonelaje = null;

    protected $listeners = ['seleccionado' => 'onSeleccionado'];

    public function mount($id = null)
    {
        // Edición de abierta: precarga todo (diagnóstico base + servicios parseados + líneas).
        if ($id) {
            $ot = WorkOrder::with('lineas')->findOrFail($id);
            abort_if(in_array($ot->estado, ['cerrada', 'cerrada-pagada', 'cerrada-pendiente', 'anulada']), 422, 'Orden cerrada/anulada: use complementaria.');
            $this->editId = $ot->id;
            $this->client_id = $ot->client_id; $this->vehicle_id = $ot->vehicle_id;
            $this->diagnostico = explode("\n", $ot->diagnostico ?? '')[0] ?? '';
            $this->mano_obra = 0;
            $this->servicios = [];
            foreach (explode("\n", $ot->diagnostico ?? '') as $ln) {
                if (preg_match('/^- (.*) \(([\d.]+)\)$/', trim($ln), $m)) $this->servicios[] = ['descripcion' => $m[1], 'precio' => (float) $m[2]];
            }
            $this->lineas = $ot->lineas->map(fn ($l) => ['product_id' => $l->product_id, 'cantidad' => (float) $l->cantidad, 'precio' => (float) $l->precio])->toArray();
            $this->usarInsumos = ! empty($this->lineas);
            return;
        }
        // Pre-selección cajero: ?cliente= &vehiculo= (Atender este vehículo) y ?complementa= (orden cerrada).
        $q = request()->query();
        if (! empty($q['cliente'])) $this->client_id = (int) $q['cliente'];
        if (! empty($q['vehiculo'])) $this->vehicle_id = (int) $q['vehiculo'];
        if (! empty($q['complementa'])) {
            $p = WorkOrder::findOrFail((int) $q['complementa']);
            $this->parent_id = $p->id; $this->client_id = $p->client_id; $this->vehicle_id = $p->vehicle_id;
        }
    }

    public function onSeleccionado($modelo, $id, $indice = null)
    {
        if ($modelo === 'Client') $this->elegirCliente($id);
        $this->alElegirProducto('lineas', $modelo, $id, $indice, 'precio');
    }

    public function elegirCliente($id)
    {
        $this->client_id = $id;
        $this->vehicle_id = null; // el vehículo siempre es hijo del cliente elegido
        $this->showAddVehiculo = false;
    }

    /** Vehículos solo del cliente elegido (sin buscador global). */
    public function vehiculosDelCliente()
    {
        if (! $this->client_id) return collect();
        return Vehicle::where('client_id', $this->client_id)->orderBy('placa')->get();
    }

    public function updatedUsarInsumos($v)
    {
        if ($v && empty($this->lineas)) $this->lineas[] = ['product_id' => null, 'cantidad' => 1, 'precio' => 0];
        if (! $v) $this->lineas = [];
    }

    public function addLinea() { $this->lineas[] = ['product_id' => null, 'cantidad' => 1, 'precio' => 0]; }
    public function addServicio() { $this->servicios[] = ['descripcion' => '', 'precio' => 0]; }
    public function quitarServicio(int $i) { unset($this->servicios[$i]); $this->servicios = array_values($this->servicios); }

    public function getTotalServiciosProperty(): float
    {
        return (float) collect($this->servicios)->sum(fn ($s) => (float) ($s['precio'] ?? 0));
    }

    public function getTotalInsumosProperty(): float
    {
        return (float) collect($this->lineas)->sum(fn ($l) => (float) ($l['cantidad'] ?? 0) * (float) ($l['precio'] ?? 0));
    }

    public function getTotalProperty(): float
    {
        return $this->mano_obra + $this->totalServicios + $this->totalInsumos;
    }

    public function guardarCliente(GuardarClienteUseCase $guardar)
    {
        $this->validate(['c_nombre' => 'required|min:3', 'c_nit_ci' => 'required']);
        $c = $guardar->ejecutar(null, ['nombre' => $this->c_nombre, 'nit_ci' => $this->c_nit_ci, 'telefono' => $this->c_telefono, 'email' => $this->c_email, 'direccion' => $this->c_direccion], []);
        $this->elegirCliente($c->id);
        $this->modalCliente = false;
        $this->reset(['c_nombre', 'c_nit_ci', 'c_telefono', 'c_email', 'c_direccion']);
    }

    /** Alta de vehículo ya ligado al cliente (retorno con otro vehículo). */
    public function guardarVehiculo(GuardarVehiculoUseCase $guardar)
    {
        $this->validate(['client_id' => 'required|exists:clients,id', 'v_placa' => 'required']);
        try {
            $v = $guardar->ejecutar(null, ['client_id' => $this->client_id, 'placa' => $this->v_placa, 'marca' => $this->v_marca, 'modelo' => $this->v_modelo, 'chasis' => $this->v_chasis, 'tonelaje' => $this->v_tonelaje]);
        } catch (\DomainException $e) {
            $this->addError('v_placa', $e->getMessage());
            return;
        }
        $this->vehicle_id = $v->id;
        $this->showAddVehiculo = false;
        $this->reset(['v_placa', 'v_marca', 'v_modelo', 'v_chasis', 'v_tonelaje']);
    }

    public function guardar(CrearOrdenUseCase $crear, EditarOrdenUseCase $editar)
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'servicios.*.descripcion' => 'nullable',
            'servicios.*.precio' => 'nullable|numeric|min:0',
        ]);
        try {
            if ($this->editId) {
                $editar->ejecutar($this->editId, $this->diagnostico, $this->mano_obra, $this->servicios, $this->lineas);
                $msg = 'Orden actualizada (auditoría registrada).';
            } else {
                $crear->ejecutar($this->client_id, $this->vehicle_id, $this->diagnostico, $this->mano_obra, $this->servicios, $this->lineas, $this->parent_id);
                $msg = 'Orden de servicio creada.';
            }
        } catch (\DomainException $e) {
            $this->addError('vehicle_id', $e->getMessage());
            return;
        }
        session()->flash('ok', $msg);
        $this->redirect('/servicios', navigate: true);
    }

    public function render()
    {
        $ids = collect($this->lineas)->map(fn ($l) => $l['product_id'] ?? null)->filter()->values();
        $prods = $ids->isEmpty() ? collect() : Product::whereIn('id', $ids)->get();
        return view('livewire.servicio-form', [
            'vehiculosCliente' => $this->vehiculosDelCliente(),
            'costos' => $prods->pluck('precio_compra', 'id')->toArray(),
            'ventas' => $prods->pluck('precio_venta', 'id')->toArray(),
        ]);
    }
}
