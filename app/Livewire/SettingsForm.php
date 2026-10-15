<?php

namespace App\Livewire;

use App\Application\Services\SettingService;
use Livewire\Component;

class SettingsForm extends Component
{
    public array $form = [];

    public function mount(SettingService $s)
    {
        $this->form = [
            'siat.driver' => $s->get('siat.driver', config('siat.driver')),
            'siat.base_url' => $s->get('siat.base_url', config('siat.base_url')),
            'siat.token' => $s->get('siat.token', ''),
            'siat.ambiente' => $s->get('siat.ambiente', 'piloto'),
            'printing.default' => $s->get('printing.default', 'pdf'),
            'taller.empresa.nombre' => $s->get('taller.empresa.nombre', ''),
        ];
    }

    public function guardar(SettingService $s)
    {
        foreach ($this->form as $k => $v) { $s->set($k, $v); }
        session()->flash('ok', 'Configuración guardada (sin hardcodeo, con caché).');
    }

    public function render() { return view('livewire.settings-form'); }
}
