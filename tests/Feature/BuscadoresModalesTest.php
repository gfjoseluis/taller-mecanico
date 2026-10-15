<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class BuscadoresModalesTest extends TestCase
{
    public function test_linea_producto_por_buscador_con_indice(): void
    {
        $this->actingAs(User::first());
        $prod = Product::create(['sku' => 'BUS-'.time(), 'nombre' => 'Buscador T', 'tipo' => 'repuesto', 'precio_venta' => 90, 'precio_compra' => 60, 'stock' => 5]);
        Livewire::test('servicio-form')
            ->set('usarInsumos', true)
            ->call('onSeleccionado', 'Product', $prod->id, 0)
            ->assertSet('lineas.0.product_id', $prod->id)
            ->assertSet('lineas.0.precio', 90.0);
        Livewire::test('compra-form')
            ->call('onSel', 'Product', $prod->id, 0)
            ->assertSet('lineas.0.product_id', $prod->id)
            ->assertSet('lineas.0.costo', 60.0);
        $prod->forceDelete();
    }

    public function test_modales_dismissables_y_menu(): void
    {
        $this->actingAs(User::first());
        $html = file_get_contents(resource_path('views/livewire/servicio-form.blade.php'));
        $this->assertStringContainsString('wire:click.self', $html);
        $this->assertStringContainsString('✕', $html);
        $this->assertStringContainsString('keydown.escape', $html);
        $menu = file_get_contents(resource_path('views/layouts/admin.blade.php'));
        $this->assertStringNotContainsString('/servicios/crear', $menu);
        $this->assertStringContainsString('/clientes', $menu);
        $this->assertStringContainsString('/vehiculos', $menu);
        // selects con buscador, sin <select> de scroll en líneas
        $this->assertStringContainsString('searchable-select', file_get_contents(resource_path('views/livewire/compra-form.blade.php')));
        $this->assertStringNotContainsString('lineas.0.product_id" class="border', file_get_contents(resource_path('views/livewire/compra-form.blade.php')));
    }
}
