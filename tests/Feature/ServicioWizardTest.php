<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Livewire\Livewire;
use Tests\TestCase;

class ServicioWizardTest extends TestCase
{
    public function test_flujo_cajero_cliente_existente_agrega_otro_vehiculo(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cajero Cli', 'nit_ci' => 'CJ-'.time()]);
        Vehicle::create(['client_id' => $cli->id, 'placa' => 'VIE-'.random_int(100, 999).substr(time(), -4)]);

        $t = Livewire::test('servicio-form')->call('elegirCliente', $cli->id);
        $this->assertCount(1, $t->viewData('vehiculosCliente'));

        $nueva = 'NUE-'.random_int(1000, 9999).substr(time(), -3);
        Livewire::test('servicio-form')
            ->set('client_id', $cli->id)
            ->set('v_placa', $nueva)->set('v_marca', 'Scania')
            ->call('guardarVehiculo')
            ->assertSet('vehicle_id', Vehicle::where('placa', $nueva)->first()->id);
        $this->assertEquals(2, $cli->fresh()->vehicles()->count());
    }

    public function test_vehiculo_de_otro_cliente_es_rechazado(): void
    {
        $this->actingAs(User::first());
        $a = Client::create(['nombre' => 'Cli A', 'nit_ci' => 'A-'.time()]);
        $b = Client::create(['nombre' => 'Cli B', 'nit_ci' => 'B-'.time()]);
        $vB = Vehicle::create(['client_id' => $b->id, 'placa' => 'MEZ-'.random_int(100, 999).substr(time(), -4)]);
        Livewire::test('servicio-form')
            ->set('client_id', $a->id)->set('vehicle_id', $vB->id)
            ->call('guardar')
            ->assertHasErrors(['vehicle_id']);
        $this->assertEquals(0, WorkOrder::where('client_id', $a->id)->count());
    }

    public function test_servicios_multiples_suman_total(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Srv', 'nit_ci' => 'S-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'SRV-'.random_int(100, 999).substr(time(), -4)]);
        $prod = Product::create(['sku' => 'WIZ-'.time(), 'nombre' => 'Aceite Wiz', 'tipo' => 'insumo', 'precio_venta' => 60, 'stock' => 10]);
        Livewire::test('servicio-form')
            ->set('client_id', $cli->id)->set('vehicle_id', $veh->id)
            ->set('diagnostico', 'Mantenimiento')
            ->call('addServicio')
            ->set('servicios.0.descripcion', 'Cambio de aceite')
            ->set('servicios.0.precio', 200)
            ->set('usarInsumos', true)
            ->set('lineas', [['product_id' => $prod->id, 'cantidad' => 2, 'precio' => 60]])
            ->call('guardar')
            ->assertRedirect('/servicios');
        $ot = WorkOrder::where('client_id', $cli->id)->latest()->firstOrFail();
        $this->assertEquals(200, (float) $ot->mano_obra);
        $this->assertEquals(120, (float) $ot->total_repuestos);
        $this->assertEquals(320, (float) $ot->total);
    }
}
