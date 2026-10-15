<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class GlosarioTest extends TestCase
{
    public function test_compra_actualiza_ultimo_costo_y_margen(): void
    {
        $this->actingAs(User::first());
        $sup = Supplier::create(['nombre' => 'Sup Costo']);
        $prod = Product::create(['sku' => 'COS-'.time(), 'nombre' => 'Costo T', 'tipo' => 'repuesto', 'precio_compra' => 50, 'precio_venta' => 100, 'stock' => 0]);
        Livewire::test('compra-form')
            ->set('supplier_id', $sup->id)
            ->set('lineas', [['product_id' => $prod->id, 'cantidad' => 4, 'costo' => 80]])
            ->call('guardar')
            ->assertRedirect('/compras');
        $this->assertEquals(80, (float) $prod->fresh()->precio_compra); // último costo auto
        $this->assertEquals(20, (float) ($prod->fresh()->precio_venta - $prod->fresh()->precio_compra)); // margen
    }

    public function test_producto_editar_no_pisa_costo(): void
    {
        $this->actingAs(User::first());
        $prod = Product::create(['sku' => 'NOC-'.time(), 'nombre' => 'NoPisa T', 'tipo' => 'repuesto', 'precio_compra' => 80, 'precio_venta' => 100, 'stock' => 0]);
        Livewire::test('product-form', ['id' => $prod->id])
            ->set('precio_venta', 150)
            ->call('guardar')
            ->assertRedirect('/productos');
        $this->assertEquals(80, (float) $prod->fresh()->precio_compra);
        $this->assertEquals(150, (float) $prod->fresh()->precio_venta);
    }

    public function test_labels_glosario(): void
    {
        foreach ([
            'views/livewire/compra-form.blade.php' => 'Costo unitario',
            'views/livewire/servicio-form.blade.php' => 'Precio aplicado',
            'views/livewire/product-form.blade.php' => 'Último costo',
            'views/livewire/product-table.blade.php' => 'Margen',
        ] as $v => $txt) {
            $this->assertStringContainsString($txt, file_get_contents(resource_path($v)), $v);
        }
    }
}
