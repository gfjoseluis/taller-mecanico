<?php

namespace Tests\Feature;

use App\Application\UseCases\AbonarCompraUseCase;
use App\Application\UseCases\AbonarOrdenUseCase;
use App\Application\UseCases\AnotarAdelantoUseCase;
use App\Application\UseCases\CobrarOrdenUseCase;
use App\Application\UseCases\CrearOrdenUseCase;
use App\Application\UseCases\GuardarClienteUseCase;
use App\Application\UseCases\GuardarProductoUseCase;
use App\Application\UseCases\RegistrarCompraUseCase;
use App\Models\BankAccount;
use App\Models\JournalLine;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicle;
use Tests\TestCase;

/** Un día del taller de punta a punta: compra → orden → adelanto → cobro → crédito saldado. */
class FlujoTallerTest extends TestCase
{
    public function test_dia_completo_cuadra(): void
    {
        $this->actingAs(User::first());
        $suf = time().random_int(10, 99);

        // 1. Banco con dinero, proveedor y producto que nace en 0.
        $banco = BankAccount::create(['nombre' => 'T-Flujo', 'banco' => 'T', 'numero' => 'TF-'.$suf, 'saldo' => 15000, 'saldo_inicial' => 15000, 'corte_at' => now()]);
        $sup = Supplier::create(['nombre' => 'Sup Flujo '.$suf]);
        $prod = app(GuardarProductoUseCase::class)->ejecutar(null, ['nombre' => 'Filtro Flujo', 'category_id' => \App\Models\Category::firstOrCreate(['nombre' => 'Filtros Flujo'], ['prefijo' => 'FLF', 'tipo' => 'repuesto'])->id, 'precio_compra' => 0, 'precio_venta' => 150, 'stock_minimo' => 2]);
        $this->assertEquals(0, (float) $prod->stock);

        // 2. Compra al contado: stock sube, banco baja, nace pagada.
        $compra = app(RegistrarCompraUseCase::class)->ejecutar($sup->id, $banco->id, 'contado', 'Flujo', [['product_id' => $prod->id, 'cantidad' => 10, 'costo' => 100]]);
        $this->assertEquals('pagada', $compra->fresh()->estado);
        $this->assertEquals(10, (float) $prod->fresh()->stock);
        $this->assertEquals(14000, (float) $banco->fresh()->saldo);

        // 3. Cliente + vehículo + orden con insumos: stock baja, total bien sumado.
        $cli = app(GuardarClienteUseCase::class)->ejecutar(null, ['nombre' => 'Cli Flujo', 'nit_ci' => 'FL-'.$suf], []);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'FLU-'.substr($suf, -6)]);
        $ot = app(CrearOrdenUseCase::class)->ejecutar($cli->id, $veh->id, 'Flujo', 500, [['descripcion' => 'Mano extra', 'precio' => 0]], [['product_id' => $prod->id, 'cantidad' => 2, 'precio' => 150]]);
        $this->assertEquals(800, (float) $ot->fresh()->total);
        $this->assertEquals(8, (float) $prod->fresh()->stock);

        // 4. Adelanto: sigue abierta, falta correcta, dinero en banco.
        app(AnotarAdelantoUseCase::class)->ejecutar($ot->id, 'efectivo', null, 300);
        $this->assertEquals('registrada', $ot->fresh()->estado);
        $this->assertEquals(500, app(AnotarAdelantoUseCase::class)->falta($ot->fresh()));

        // 5. Cobro del saldo: cerrada y pagada, todo lo recibido suma el total.
        app(CobrarOrdenUseCase::class)->ejecutar($ot->id, 'efectivo', null, 500);
        $this->assertEquals('cerrada-pagada', $ot->fresh()->estado);
        $this->assertEquals(800, (float) $ot->payments()->sum('recibido'));

        // 6. Segunda orden a crédito + abonos hasta saldar: Por cobrar queda en 0.
        $ot2 = app(CrearOrdenUseCase::class)->ejecutar($cli->id, $veh->id, 'Flujo 2', 400, [], []);
        app(CobrarOrdenUseCase::class)->ejecutar($ot2->id, 'credito', null, 0);
        app(AbonarOrdenUseCase::class)->ejecutar($ot2->id, 'efectivo', null, 150);
        $this->assertEquals(250, app(AbonarOrdenUseCase::class)->pendiente($ot2->fresh()));
        app(AbonarOrdenUseCase::class)->ejecutar($ot2->id, 'efectivo', null, 250);
        $this->assertEquals('cerrada-pagada', $ot2->fresh()->estado);

        // 7. Compra a crédito + abono: nace vigente, al saldar queda pagada.
        $compra2 = app(RegistrarCompraUseCase::class)->ejecutar($sup->id, null, 'credito', 'Flujo cred', [['product_id' => $prod->id, 'cantidad' => 1, 'costo' => 100]]);
        $this->assertEquals('vigente', $compra2->fresh()->estado);
        app(AbonarCompraUseCase::class)->ejecutar($compra2->id, 'efectivo', null, (float) $compra2->fresh()->total);
        $this->assertEquals('pagada', $compra2->fresh()->estado);

        // 8. Invariantes: todo asiento cuadra y ninguna cuenta en negativo.
        $this->assertEquals(
            (float) JournalLine::sum('debe'),
            (float) JournalLine::sum('haber'),
            'El diario no cuadra'
        );
        $this->assertEquals(0, BankAccount::where('saldo', '<', 0)->count(), 'Hay cuentas en negativo');
    }
}
