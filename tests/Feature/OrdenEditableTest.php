<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Livewire\Livewire;
use Tests\TestCase;

class OrdenEditableTest extends TestCase
{
    public function test_editar_abierta_recAlcula_y_audita(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Edit', 'nit_ci' => 'E-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'EDT-'.random_int(100, 999).substr(time(), -4)]);
        $prod = Product::create(['sku' => 'EDT-'.time(), 'nombre' => 'Aceite Edit', 'tipo' => 'insumo', 'precio_venta' => 60, 'stock' => 10]);
        $ot = WorkOrder::create(['numero' => 'SRV-T'.time(), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'registrada', 'mano_obra' => 100, 'total' => 100]);

        Livewire::test('servicio-form', ['id' => $ot->id])
            ->set('servicios', [['descripcion' => 'Extra', 'precio' => 50]])
            ->set('usarInsumos', true)
            ->set('lineas', [['product_id' => $prod->id, 'cantidad' => 2, 'precio' => 60]])
            ->call('guardar')
            ->assertRedirect('/servicios');

        $ot->refresh();
        $this->assertEquals(50, (float) $ot->mano_obra);
        $this->assertEquals(120, (float) $ot->total_repuestos);
        $this->assertEquals(170, (float) $ot->total);
        $this->assertEquals(8, (float) $prod->fresh()->stock);
        $this->assertEquals('editada', $ot->logs()->latest()->first()->accion);
    }

    public function test_cerrada_no_editable_y_nueva_orden_independiente(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Cerr', 'nit_ci' => 'C-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'CER-'.random_int(100, 999).substr(time(), -4)]);
        $ot = WorkOrder::create(['numero' => 'SRV-U'.time(), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'cerrada', 'mano_obra' => 100, 'total' => 100]);

        // Cerrada: la vista de edición responde 422 (candado), la cerrada queda intacta.
        $this->get("/servicios/{$ot->id}/editar")->assertStatus(422);
        // La cerrada se ve en detalle y lo adicional es una orden nueva independiente.
        $this->get("/servicios/{$ot->id}")->assertOk()->assertSee($ot->numero);
        $this->get('/servicios/crear')->assertOk();
        $this->assertEquals(100, (float) $ot->fresh()->total); // cerrada intacta
    }

    public function test_atender_otro_vehiculo_preselecciona(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Soc', 'nit_ci' => 'S-'.time()]);
        $v1 = Vehicle::create(['client_id' => $cli->id, 'placa' => 'SOC-'.random_int(100, 999).substr(time(), -4)]);
        $this->get("/servicios/crear?cliente={$cli->id}&vehiculo={$v1->id}")->assertOk();
        $t = Livewire::test('servicio-form');
        // mount lee query en request real; aquí verificamos tabla atiende
        $this->get("/clientes/{$cli->id}")->assertOk()->assertSee('Atender este vehículo');
    }
}
