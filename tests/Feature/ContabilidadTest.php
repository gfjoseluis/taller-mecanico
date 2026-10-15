<?php

namespace Tests\Feature;

use App\Application\Services\AsientoService;
use App\Models\ChartAccount;
use App\Models\Client;
use App\Models\JournalEntry;
use App\Models\Periodo;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Livewire\Livewire;
use Tests\TestCase;

class ContabilidadTest extends TestCase
{
    public function test_venta_genera_registro_cuadrado(): void
    {
        $this->actingAs(User::first());
        $n = JournalEntry::count();
        $cli = Client::create(['nombre' => 'Cli Cont', 'nit_ci' => 'CT-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'CON-'.random_int(100, 999).substr(time(), -4)]);
        $ot = WorkOrder::create(['numero' => 'SRV-K'.time(), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'registrada', 'mano_obra' => 200, 'total_repuestos' => 100, 'total' => 300]);
        Livewire::test('cobro-form', ['id' => $ot->id])->set('medio', 'efectivo')->set('recibido', 300)->call('guardar');
        $a = JournalEntry::where('referencia', $ot->numero)->firstOrFail();
        $this->assertEquals($n + 1, JournalEntry::count());
        $this->assertEquals($a->lines->sum('debe'), $a->lines->sum('haber'));
        $this->assertEquals(300, $a->lines->sum('debe'));
    }

    public function test_compra_credito_y_abono_cuadran(): void
    {
        $this->actingAs(User::first());
        $n = JournalEntry::count();
        $sup = Supplier::create(['nombre' => 'Sup Cont']);
        $prod = Product::create(['sku' => 'CTC-'.time(), 'nombre' => 'Cont T', 'tipo' => 'repuesto', 'precio_venta' => 100, 'stock' => 0]);
        Livewire::test('compra-form')->set('supplier_id', $sup->id)->set('tipo_pago', 'credito')
            ->set('lineas', [['product_id' => $prod->id, 'cantidad' => 2, 'costo' => 50]])->call('guardar');
        $c = \App\Models\Purchase::where('supplier_id', $sup->id)->latest()->first();
        Livewire::test('abono-proveedor-form', ['id' => $c->id])->set('medio', 'efectivo')->set('monto', 100)->call('guardar');
        $this->assertEquals($n + 2, JournalEntry::count());
        foreach (JournalEntry::latest()->limit(2)->get() as $a) {
            $this->assertEquals($a->lines->sum('debe'), $a->lines->sum('haber'));
        }
    }

    public function test_manual_descuadrado_no_guarda_y_mes_cerrado_bloquea(): void
    {
        $this->actingAs(User::first());
        $cta = ChartAccount::where('imputable', true)->first();
        $n = JournalEntry::count();
        Livewire::test('asiento-form')
            ->set('glosa', 'Mal')
            ->set('lineas', [['account_id' => $cta->id, 'debe' => 100, 'haber' => 0], ['account_id' => $cta->id, 'debe' => 0, 'haber' => 50]])
            ->call('guardar')->assertHasErrors(['lineas']);
        $this->assertEquals($n, JournalEntry::count());

        Periodo::updateOrCreate(['gestion' => 2020, 'mes' => 1], ['cerrado' => true]);
        $this->expectException(\RuntimeException::class);
        app(AsientoService::class)->registrar('Bloqueado', 'manual', null, [
            ['account_id' => $cta->id, 'debe' => 10, 'haber' => 0],
            ['account_id' => $cta->id, 'debe' => 0, 'haber' => 10],
        ], '2020-01-15');
    }

    public function test_reportes_responden(): void
    {
        $this->actingAs(User::first());
        foreach (['/contabilidad', '/contabilidad/mayor', '/contabilidad/balance', '/contabilidad/resultados', '/contabilidad/equivalencias', '/contabilidad/asiento', '/contabilidad/cierre', '/contabilidad/balance/csv'] as $u) {
            $this->get($u)->assertOk();
        }
    }
}
