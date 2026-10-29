<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;
use Tests\TestCase;

class CambiosTest extends TestCase
{
    public function test_servicio_con_insumos_descuenta_stock(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli T', 'nit_ci' => 'T-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'TST-'.random_int(100, 999).substr(time(), -4)]);
        $prod = Product::create(['sku' => 'TST-'.time(), 'nombre' => 'Insumo T', 'tipo' => 'insumo', 'precio_venta' => 50, 'stock' => 10]);
        Livewire::test('servicio-form')
            ->set('client_id', $cli->id)
            ->set('vehicle_id', $veh->id)
            ->set('diagnostico', 'Mantenimiento')
            ->set('mano_obra', 100)
            ->set('usarInsumos', true)
            ->set('lineas', [['product_id' => $prod->id, 'cantidad' => 2, 'precio' => 50]])
            ->call('guardar')
            ->assertRedirect('/servicios');
        $this->assertEquals(8, (float) $prod->fresh()->stock);
    }

    public function test_servicio_sin_stock_bloquea_con_disponible(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli S0', 'nit_ci' => 'S0-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'S00-'.random_int(100, 999).substr(microtime(true) * 10000, -4)]);
        $prod = Product::create(['sku' => 'ST0-'.time(), 'nombre' => 'Caja Mecánica T', 'tipo' => 'repuesto', 'precio_venta' => 500, 'stock' => 0]);
        Livewire::test('servicio-form')
            ->set('client_id', $cli->id)
            ->set('vehicle_id', $veh->id)
            ->set('diagnostico', 'Mantenimiento')
            ->set('mano_obra', 100)
            ->set('usarInsumos', true)
            ->set('lineas', [['product_id' => $prod->id, 'cantidad' => 1, 'precio' => 500]])
            ->call('guardar')
            ->assertHasErrors(['lineas']);
        $this->assertEquals(0, (float) $prod->fresh()->stock);
        $this->assertEquals(0, \App\Models\WorkOrder::where('client_id', $cli->id)->count());
    }

    public function test_compra_suma_stock(): void
    {
        $this->actingAs(User::first());
        $sup = Supplier::create(['nombre' => 'Sup T']);
        $prod = Product::create(['sku' => 'CMP-'.time(), 'nombre' => 'Rep T', 'tipo' => 'repuesto', 'precio_venta' => 10, 'stock' => 0]);
        Livewire::test('compra-form')
            ->set('supplier_id', $sup->id)
            ->set('lineas', [['product_id' => $prod->id, 'cantidad' => 3, 'costo' => 5]])
            ->call('guardar')
            ->assertRedirect('/compras');
        $this->assertEquals(3, (float) $prod->fresh()->stock);
    }

    public function test_usuarios_roles_y_contabilidad_export(): void
    {
        $this->actingAs(User::first());
        $this->get('/usuarios')->assertOk();
        $this->get('/roles')->assertOk();
        $this->get('/clientes/'.Client::first()->id)->assertOk();
        $this->get('/contabilidad/csv')->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->get('/contabilidad/imprimir')->assertOk();
    }
}
