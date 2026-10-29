<?php

namespace Tests\Feature;

use App\Application\UseCases\AnotarAdelantoUseCase;
use App\Models\BankAccount;
use App\Models\Client;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Livewire\Livewire;
use Tests\TestCase;

/** Adelantos: dinero en orden abierta, al cerrar solo se pide lo que falta. */
class AdelantoTest extends TestCase
{
    private function ordenAbierta(float $total = 500): WorkOrder
    {
        $cli = Client::create(['nombre' => 'Cli Adel', 'nit_ci' => 'AD-'.time().random_int(1000, 9999)]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'ADE-'.random_int(100, 999).substr(microtime(true) * 10000, -4)]);
        return WorkOrder::create(['numero' => 'SRV-A'.time().random_int(1000, 9999), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'registrada', 'mano_obra' => $total, 'total' => $total]);
    }

    public function test_adelanto_deja_orden_abierta_y_resta_falta(): void
    {
        $this->actingAs(User::first());
        $ot = $this->ordenAbierta();
        $uc = app(AnotarAdelantoUseCase::class);
        $uc->ejecutar($ot->id, 'efectivo', null, 200);

        $ot->refresh();
        $this->assertEquals('registrada', $ot->estado);
        $this->assertEquals(200, $uc->adelantado($ot));
        $this->assertEquals(300, $uc->falta($ot));
        $this->assertEquals('adelanto', $ot->logs()->latest()->first()->accion);
        // Dinero entró a caja y el asiento cuadra (caja vs adelantos).
        $this->assertDatabaseHas('bank_movements', ['origen_tipo' => 'adelanto-cliente', 'origen_id' => $ot->id]);
        $this->assertDatabaseHas('journal_entries', ['glosa' => "Adelanto cliente {$ot->numero}"]);
    }

    public function test_adelanto_mayor_que_falta_falla(): void
    {
        $this->actingAs(User::first());
        $ot = $this->ordenAbierta(500);
        $this->expectException(\DomainException::class);
        app(AnotarAdelantoUseCase::class)->ejecutar($ot->id, 'efectivo', null, 600);
    }

    public function test_flujo_unico_recibe_menos_es_adelanto_y_luego_cierra(): void
    {
        $this->actingAs(User::first());
        $ot = $this->ordenAbierta(500);

        // Recibe 200 de 500: adelanto, sigue abierta.
        Livewire::test('cobro-form', ['id' => $ot->id])
            ->set('medio', 'efectivo')->set('recibido', 200)
            ->call('guardar')->assertRedirect('/servicios');
        $ot->refresh();
        $this->assertEquals('registrada', $ot->estado);

        // Recibe 300 de 300 que faltan: cierra.
        Livewire::test('cobro-form', ['id' => $ot->id])
            ->assertSet('recibido', 300.0)
            ->set('medio', 'efectivo')->set('recibido', 300)
            ->call('guardar')->assertRedirect('/servicios');

        $ot->refresh();
        $this->assertEquals('cerrada-pagada', $ot->estado);
        $this->assertEquals(500, (float) $ot->payments()->sum('recibido'));
    }

    public function test_recibe_cero_pide_monto_o_deuda(): void
    {
        $this->actingAs(User::first());
        $ot = $this->ordenAbierta(500);
        Livewire::test('cobro-form', ['id' => $ot->id])
            ->set('medio', 'efectivo')->set('recibido', 0)
            ->call('guardar')->assertHasErrors(['recibido']);
        $this->assertEquals('registrada', $ot->fresh()->estado);
    }

    public function test_queda_debiendo_tras_adelanto_debe_solo_falta(): void
    {
        $this->actingAs(User::first());
        $ot = $this->ordenAbierta(500);
        app(AnotarAdelantoUseCase::class)->ejecutar($ot->id, 'efectivo', null, 200);
        Livewire::test('cobro-form', ['id' => $ot->id])
            ->call('quedaDebiendo')->assertRedirect('/por-cobrar');
        $ot->refresh();
        $this->assertEquals('cerrada-pendiente', $ot->estado);
        $this->assertEquals(300, (float) $ot->payments()->where('saldo_pendiente', '>', 0)->first()->saldo_pendiente);
    }

    public function test_anular_con_adelanto_se_bloquea(): void
    {
        $this->actingAs(User::first());
        $ot = $this->ordenAbierta();
        app(AnotarAdelantoUseCase::class)->ejecutar($ot->id, 'efectivo', null, 100);
        $this->expectException(\DomainException::class);
        app(\App\Application\UseCases\AnularOrdenUseCase::class)->ejecutar($ot->id);
    }

    public function test_pantalla_cobrar_muestra_total_ya_dio_falta_y_deuda(): void
    {
        $this->actingAs(User::first());
        $ot = $this->ordenAbierta(500);
        $this->get("/servicios/{$ot->id}/cobrar")->assertOk()->assertSee('Ya dio')->assertSee('Falta')->assertSee('Queda debiendo');
        $this->get("/servicios/{$ot->id}/adelanto")->assertNotFound();
    }
}
