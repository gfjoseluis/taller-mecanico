<?php

namespace App\Livewire;

use App\Application\Concerns\NormalizaVacios;
use App\Application\UseCases\GuardarFotoUseCase;
use App\Application\UseCases\GuardarRecepcionUseCase;
use App\Models\Recepcion;
use App\Models\RecepcionFoto;
use App\Models\WorkOrder;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Recepción del vehículo: kilometraje, combustible, inventario, inspección y 5 fotos. */
class RecepcionForm extends Component
{
    use WithFileUploads;
    use NormalizaVacios;
    public WorkOrder $orden;
    public $kilometraje = null;
    public string $combustible = 'medio';
    public array $inventario = [];
    public string $inventario_otros = '';
    public array $inspeccion = [];
    public string $observaciones = '';
    public array $subidas = [];

    /** Foto ya subida al temporal: valida, normaliza y guarda. */
    public function procesarFoto(string $posicion, GuardarFotoUseCase $casos)
    {
        $this->validate(["subidas.{$posicion}" => 'required|image|max:5120']);
        try {
            $casos->ejecutar($this->orden->id, $posicion, $this->subidas[$posicion]->getRealPath());
        } catch (\DomainException $e) {
            $this->addError('subidas', $e->getMessage());
            return;
        }
        unset($this->subidas[$posicion]);
        $this->orden->load('recepcion.fotos');
        session()->flash('ok', 'Foto guardada (optimizada).');
    }

    public function mount(int $id)
    {
        $this->orden = WorkOrder::with(['client', 'vehicle', 'recepcion.fotos'])->findOrFail($id);
        abort_if($this->orden->estado !== 'registrada', 422, 'Solo órdenes abiertas tienen recepción.');
        if ($this->orden->recepcion) {
            $r = $this->orden->recepcion;
            $this->kilometraje = $r->kilometraje;
            $this->combustible = $r->combustible;
            $this->inventario = $r->inventario ?? [];
            $this->inventario_otros = $r->inventario_otros ?? '';
            $this->inspeccion = $r->inspeccion ?? [];
            $this->observaciones = $r->observaciones ?? '';
        }
    }

    public function guardar(GuardarRecepcionUseCase $guardar)
    {
        $this->vaciosANulo('kilometraje');
        $this->validate(['observaciones' => 'nullable|max:2000', 'inventario_otros' => 'nullable|max:255', 'kilometraje' => 'nullable|integer|min:0']);
        try {
            $guardar->ejecutar($this->orden->id, $this->combustible, $this->inventario, $this->inventario_otros ?: null, $this->inspeccion, $this->observaciones ?: null, $this->kilometraje ?: null);
        } catch (\DomainException $e) {
            $this->addError('combustible', $e->getMessage());
            return;
        }
        session()->flash('ok', 'Recepción guardada.');
        $this->redirect('/servicios/'.$this->orden->id, navigate: true);
    }

    public function render()
    {
        $fotos = $this->orden->recepcion ? $this->orden->recepcion->fotos->pluck('ruta', 'posicion')->toArray() : [];
        return view('livewire.recepcion-form', ['invItems' => Recepcion::INVENTARIO, 'insItems' => Recepcion::INSPECCION, 'combOpciones' => Recepcion::COMBUSTIBLE, 'posiciones' => RecepcionFoto::POSICIONES, 'guias' => RecepcionFoto::GUIAS, 'fotos' => $fotos]);
    }
}
