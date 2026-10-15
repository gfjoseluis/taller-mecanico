<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class CeroMemorizacionTest extends TestCase
{
    public function test_precio_autocompleta_y_avisa_perdida(): void
    {
        $this->actingAs(User::first());
        $prod = Product::create(['sku' => 'MEM-'.time(), 'nombre' => 'Mem T', 'tipo' => 'repuesto', 'precio_compra' => 80, 'precio_venta' => 120, 'stock' => 5]);
        $t = Livewire::test('servicio-form')
            ->set('usarInsumos', true)
            ->call('onSeleccionado', 'Product', $prod->id, 0);
        $this->assertEquals(120.0, (float) $t->get('lineas.0.precio')); // cero memorización
        $t->set('lineas.0.precio', 50)->assertSee('Vendes a pérdida');
        $t->set('lineas.0.precio', 120)->assertSee('Sugerido ficha');
        $prod->forceDelete();
    }

    public function test_buscador_muestra_venta_y_costo_opcional(): void
    {
        $s = file_get_contents(app_path('Livewire/SearchableSelect.php'));
        $this->assertStringContainsString('venta:', $s);
        $f = file_get_contents(resource_path('views/livewire/product-form.blade.php'));
        $this->assertStringContainsString('opcional', $f);
        $this->assertStringContainsString('se llena con tu primera compra', $f);
    }
}
