<?php

namespace Tests\Feature;

use App\Application\UseCases\CobrarOrdenUseCase;
use App\Application\UseCases\CrearOrdenUseCase;
use App\Models\Client;
use App\Models\JournalEntry;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\NumeroLetras;
use Livewire\Livewire;
use Tests\TestCase;

/** Descuento de papel real: resta del total, cuadra en contabilidad y sale en literal. */
class DescuentoTest extends TestCase
{
    public function test_orden_con_descuento_totaliza_y_cobra_neto(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Desc', 'nit_ci' => 'D-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'DES-'.random_int(100, 999).substr(microtime(true) * 10000, -4)]);
        // Como el papel: 2650 − 135 = 2515.
        $ot = app(CrearOrdenUseCase::class)->ejecutar($cli->id, $veh->id, 'Papel', 2650, [], [], null, 135);
        $this->assertEquals(2515, (float) $ot->fresh()->total);
        $this->assertEquals(135, (float) $ot->fresh()->descuento);

        app(CobrarOrdenUseCase::class)->ejecutar($ot->id, 'efectivo', null, 2515);
        $a = JournalEntry::where('glosa', "Venta servicio {$ot->numero}")->with('lines.account')->firstOrFail();
        $debe = $a->lines->sum('debe');
        $haber = $a->lines->sum('haber');
        $this->assertEquals($haber, $debe);
        $this->assertEquals(135, (float) $a->lines->firstWhere('account.codigo', '4.3')->debe);
    }

    public function test_descuento_mayor_que_subtotal_falla(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Desc2', 'nit_ci' => 'D2-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'DE2-'.random_int(100, 999).substr(microtime(true) * 10000, -4)]);
        $this->expectException(\DomainException::class);
        app(CrearOrdenUseCase::class)->ejecutar($cli->id, $veh->id, 'Papel', 100, [], [], null, 150);
    }

    public function test_formulario_muestra_subtotal_descuento_y_total(): void
    {
        $this->actingAs(User::first());
        Livewire::test('servicio-form')
            ->set('mano_obra', 100)
            ->set('descuento', 10)
            ->assertSet('subtotal', 100.0)
            ->assertSet('total', 90.0);
    }

    public function test_borrar_descuento_no_da_error(): void
    {
        $this->actingAs(User::first());
        $t = Livewire::test('servicio-form')
            ->set('descuento', 135)
            ->set('descuento', '')
            ->assertHasNoErrors()
            ->assertSet('descuento', null)
            ->assertSet('total', 0.0);
        $t->set('mano_obra', 200)->assertSet('total', 200.0);
    }

    public function test_literal(): void
    {
        $this->assertEquals('Dos mil quinientos quince 00/100 Bs', NumeroLetras::convertir(2515));
        $this->assertEquals('Cero 00/100 Bs', NumeroLetras::convertir(0));
        $this->assertEquals('Cien 00/100 Bs', NumeroLetras::convertir(100));
        $this->assertEquals('Un millón 00/100 Bs', NumeroLetras::convertir(1000000));
    }
}
