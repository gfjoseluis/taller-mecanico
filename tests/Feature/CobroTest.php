<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Livewire\Livewire;
use Tests\TestCase;

class CobroTest extends TestCase
{
    public function test_cobro_efectivo_con_vuelto(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Cobro', 'nit_ci' => 'CB-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'CBR-'.random_int(100, 999).substr(time(), -4)]);
        $ot = WorkOrder::create(['numero' => 'SRV-C'.time(), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'registrada', 'mano_obra' => 500, 'total' => 500]);
        Livewire::test('cobro-form', ['id' => $ot->id])
            ->set('medio', 'efectivo')->set('recibido', 600)
            ->assertSet('vuelto', 100.0)
            ->call('guardar')->assertRedirect('/servicios');
        $ot->refresh();
        $this->assertEquals('cerrada-pagada', $ot->estado);
        $this->assertEquals(100, (float) $ot->payments()->first()->vuelto);
    }

    public function test_queda_debiendo_cierra_a_credito(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli crédito', 'nit_ci' => 'F-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'FIA-'.random_int(100, 999).substr(time(), -4)]);
        $ot = WorkOrder::create(['numero' => 'SRV-F'.time(), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'registrada', 'mano_obra' => 300, 'total' => 300]);
        Livewire::test('cobro-form', ['id' => $ot->id])
            ->call('quedaDebiendo')->assertRedirect('/por-cobrar');
        $ot->refresh();
        $this->assertEquals('cerrada-pendiente', $ot->estado);
        $this->assertEquals(300, (float) $ot->payments()->first()->saldo_pendiente);
    }

    public function test_transferencia_pide_banco_y_recibo(): void
    {
        $this->actingAs(User::first());
        $b = BankAccount::first();
        $cli = Client::create(['nombre' => 'Cli Trans', 'nit_ci' => 'TR-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'TRN-'.random_int(100, 999).substr(time(), -4)]);
        $ot = WorkOrder::create(['numero' => 'SRV-T'.time(), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'registrada', 'mano_obra' => 200, 'total' => 200]);
        Livewire::test('cobro-form', ['id' => $ot->id])
            ->set('medio', 'transferencia')->set('recibido', 200)
            ->call('guardar')->assertHasErrors(['bank_account_id']);
        Livewire::test('cobro-form', ['id' => $ot->id])
            ->set('medio', 'transferencia')->set('bank_account_id', $b->id)->set('recibido', 200)
            ->call('guardar')->assertRedirect('/servicios');
        $this->get("/servicios/{$ot->id}/imprimir")->assertOk()->assertSee($ot->numero);
    }
}
