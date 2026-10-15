<?php

namespace App\Livewire;

use App\Application\UseCases\GuardarSocioUseCase;
use App\Models\Partner;
use Livewire\Component;

class PartnerForm extends Component
{
    public $editId = null;
    public string $nombre = '';
    public string $ci = '';
    public string $telefono = '';

    public function mount($id = null)
    {
        if ($id) { $s = Partner::findOrFail($id); $this->editId = $s->id; $this->nombre = $s->nombre; $this->ci = $s->ci ?? ''; $this->telefono = $s->telefono ?? ''; }
    }

    public function guardar(GuardarSocioUseCase $guardar)
    {
        $this->validate(['nombre' => 'required|min:3']);
        $guardar->ejecutar($this->editId, ['nombre' => $this->nombre, 'ci' => $this->ci, 'telefono' => $this->telefono]);
        session()->flash('ok', 'Socio guardado.');
        $this->redirect('/socios', navigate: true);
    }

    public function render() { return view('livewire.partner-form'); }
}
