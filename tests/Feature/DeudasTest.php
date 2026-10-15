<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Livewire\Livewire;
use Tests\TestCase;

class DeudasTest extends TestCase
{
    public function test_abono_cliente_cierra_deuda(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Deuda', 'nit_ci' => 'D-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'DEU-'.random_int(100, 999).substr(time(), -4)]);
        $ot = WorkOrder::create(['numero' => 'SRV-D'.time(), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'cerrada-pendiente', 'mano_obra' => 500, 'total' => 500, 'cerrada_at' => now()]);
        $ot->payments()->create(['medio' => 'credito', 'total' => 500, 'recibido' => 0, 'saldo_pendiente' => 500]);
        Livewire::test('abono-form', ['id' => $ot->id])
            ->set('medio', 'efectivo')->set('monto', 200)->call('guardar')->assertRedirect('/por-cobrar');
        $this->assertEquals('cerrada-pendiente', $ot->fresh()->estado);
        Livewire::test('abono-form', ['id' => $ot->id])
            ->set('medio', 'efectivo')->set('monto', 300)->call('guardar')->assertRedirect('/por-cobrar');
        $this->assertEquals('cerrada-pagada', $ot->fresh()->estado);
        $this->get('/por-cobrar')->assertOk();
    }

    public function test_abono_importadora_cierra_deuda(): void
    {
        $this->actingAs(User::first());
        $sup = Supplier::create(['nombre' => 'Sup Deuda']);
        $c = Purchase::create(['folio' => 'CMP-D'.time(), 'supplier_id' => $sup->id, 'tipo_pago' => 'credito', 'total' => 400, 'saldo' => 400]);
        Livewire::test('abono-proveedor-form', ['id' => $c->id])
            ->set('medio', 'efectivo')->set('monto', 400)->call('guardar')->assertRedirect('/por-pagar');
        $c->refresh();
        $this->assertEquals(0, (float) $c->saldo);
        $this->assertEquals('pagada', $c->estado);
        $this->get('/por-pagar')->assertOk();
    }
}
