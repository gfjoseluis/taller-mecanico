<?php

namespace Tests\Feature;

use App\Application\Services\MovimientoService;
use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Livewire\Livewire;
use Tests\TestCase;

class DineroTest extends TestCase
{
    public function test_compra_contado_descuenta_banco_visible(): void
    {
        $this->actingAs(User::first());
        $banco = BankAccount::create(['nombre' => 'T-Banco', 'banco' => 'T', 'numero' => 'T-'.time(), 'saldo' => 5000, 'saldo_inicial' => 5000, 'corte_at' => now()]);
        $sup = Supplier::create(['nombre' => 'Sup Din']);
        $prod = Product::create(['sku' => 'DIN-'.time(), 'nombre' => 'Din T', 'tipo' => 'repuesto', 'precio_venta' => 100, 'stock' => 0]);
        Livewire::test('compra-form')
            ->set('supplier_id', $sup->id)->set('bank_account_id', $banco->id)
            ->set('lineas', [['product_id' => $prod->id, 'cantidad' => 10, 'costo' => 120]])
            ->call('guardar')->assertRedirect('/compras');
        $this->assertEquals(3800, (float) $banco->fresh()->saldo); // 5000 - 1200 visible
        $mov = app(MovimientoService::class);
        $this->assertEquals(3800, $mov->saldoCalculado($banco->fresh())); // cuadra
    }

    public function test_cobro_suma_caja_y_traspaso_y_aporte(): void
    {
        $this->actingAs(User::first());
        $mov = app(MovimientoService::class);
        $caja = $mov->caja();
        $antes = (float) $caja->fresh()->saldo;
        $cli = Client::create(['nombre' => 'Cli Din', 'nit_ci' => 'DN-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'DIN-'.random_int(100, 999).substr(time(), -4)]);
        $ot = WorkOrder::create(['numero' => 'SRV-M'.time(), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'registrada', 'mano_obra' => 300, 'total' => 300]);
        Livewire::test('cobro-form', ['id' => $ot->id])->set('medio', 'efectivo')->set('recibido', 300)->call('guardar');
        $this->assertEquals($antes + 300, (float) $caja->fresh()->saldo);

        $b2 = BankAccount::create(['nombre' => 'T-B2', 'banco' => 'T', 'numero' => 'T2-'.time(), 'saldo' => 0, 'saldo_inicial' => 0, 'corte_at' => now()]);
        Livewire::test('movimiento-form')->set('tipo', 'traspaso')->set('cuenta_id', $caja->id)->set('cuenta_destino_id', $b2->id)->set('monto', 100)->set('motivo', 'Traspaso test')->call('guardar')->assertRedirect('/movimientos');
        $this->assertEquals($antes + 200, (float) $caja->fresh()->saldo);
        $this->assertEquals(100, (float) $b2->fresh()->saldo);

        $soc = Partner::create(['nombre' => 'Socio Test']);
        Livewire::test('movimiento-form')->set('tipo', 'aporte')->set('cuenta_id', $b2->id)->set('partner_id', $soc->id)->set('monto', 50000)->set('motivo', 'Aporte inicial')->call('guardar');
        $this->assertEquals(50100, (float) $b2->fresh()->saldo);
        $this->assertEquals(50000, $soc->fresh()->aportes);
    }

    public function test_anular_revierte_plata(): void
    {
        $this->actingAs(User::first());
        $banco = BankAccount::create(['nombre' => 'T-Rev', 'banco' => 'T', 'numero' => 'TR-'.time(), 'saldo' => 2000, 'saldo_inicial' => 2000, 'corte_at' => now()]);
        $sup = Supplier::create(['nombre' => 'Sup Rev']);
        $prod = Product::create(['sku' => 'REV-'.time(), 'nombre' => 'Rev T', 'tipo' => 'repuesto', 'precio_venta' => 50, 'stock' => 0]);
        Livewire::test('compra-form')
            ->set('supplier_id', $sup->id)->set('bank_account_id', $banco->id)
            ->set('lineas', [['product_id' => $prod->id, 'cantidad' => 2, 'costo' => 100]])
            ->call('guardar');
        $this->assertEquals(1800, (float) $banco->fresh()->saldo);
        $compra = \App\Models\Purchase::where('supplier_id', $sup->id)->latest()->first();
        Livewire::test('compra-table')->call('anular', $compra->id);
        $this->assertEquals(2000, (float) $banco->fresh()->saldo); // reversión, original intacto
        $orig = \App\Models\BankMovement::where('origen_tipo', 'compra')->where('origen_id', $compra->id)->firstOrFail();
        $this->assertTrue(\App\Models\BankMovement::where('origen_tipo', 'reversion')->where('origen_id', $orig->id)->exists());
    }
}
