<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class ProveedorModalTest extends TestCase
{
    public function test_modal_proveedor_mismos_campos_y_selecciona(): void
    {
        $this->actingAs(User::first());
        $nit = 'IMP-'.time();
        Livewire::test('compra-form')
            ->set('s_nombre', 'Importadora Modal')
            ->set('s_nit', $nit)
            ->set('s_telefono', '2820000')
            ->set('s_direccion', 'Av. Import 1')
            ->call('guardarProveedor')
            ->assertSet('modalProveedor', false);
        $s = Supplier::where('nit', $nit)->firstOrFail();
        $this->assertEquals('2820000', $s->telefono);
        $this->assertEquals('Av. Import 1', $s->direccion);
        // auto-selección: el id queda en el form y el buscador se remonta con clave
        Livewire::test('compra-form')
            ->set('s_nombre', 'Importadora Auto')
            ->set('s_nit', $nit.'-2')
            ->call('guardarProveedor')
            ->assertSet('supplier_id', Supplier::where('nit', $nit.'-2')->first()->id);
        $html = file_get_contents(resource_path('views/livewire/compra-form.blade.php'));
        $this->assertStringContainsString("components.supplier-fields", $html);
        $this->assertStringContainsString('wire:click.self', $html);
        $this->assertStringContainsString('✕', $html);
        $this->assertStringContainsString("'sup-'.\$supplier_id", $html);
    }
}
