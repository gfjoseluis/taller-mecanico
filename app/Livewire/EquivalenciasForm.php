<?php

namespace App\Livewire;

use App\Application\Services\SettingService;
use App\Models\ChartAccount;
use Livewire\Component;

/** El contador ajusta qué cuenta va con cada concepto (sin código). */
class EquivalenciasForm extends Component
{
    public array $mapa = [];
    public array $conceptos = [
        'ventas_repuestos' => 'Ventas de repuestos (Ej: 4.1)',
        'servicios' => 'Servicios de taller (Ej: 4.2)',
        'costo' => 'Costo de ventas (Ej: 5.1)',
        'inventario' => 'Mercadería en almacén (Ej: 1.2)',
        'caja' => 'Caja en efectivo (Ej: 1.1.01)',
        'bancos' => 'Cuentas de banco (Ej: 1.1.02)',
        'por_cobrar' => 'Lo que deben los clientes (Ej: 1.3)',
        'por_pagar' => 'Lo que debemos (Ej: 2.1)',
        'capital' => 'Capital de socios (Ej: 3.1)',
        'gasto' => 'Gastos operativos (Ej: 5.2)',
    ];

    public function mount(SettingService $s)
    {
        foreach ($this->conceptos as $k => $l) {
            $this->mapa[$k] = $s->get("contabilidad.mapa.{$k}", config("contabilidad.mapa.{$k}"));
        }
    }

    public function guardar(SettingService $s)
    {
        foreach ($this->mapa as $k => $codigo) {
            $this->validate(["mapa.{$k}" => 'required|exists:chart_accounts,codigo']);
            $s->set("contabilidad.mapa.{$k}", $codigo);
        }
        session()->flash('ok', 'Equivalencias guardadas. Revisado por contador.');
    }

    public function render() { return view('livewire.equivalencias-form', ['cuentas' => ChartAccount::orderBy('codigo')->get()]); }
}
