<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Tests\TestCase;

/** Pantalla única de impresión en carta (igual que Ver, con barra Imprimir). */
class ImpresionTest extends TestCase
{
    public function test_orden_ver_e_imprimir(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Imp', 'nit_ci' => 'I-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'IMP-'.random_int(100, 999).substr(time(), -4)]);
        $ot = WorkOrder::create(['numero' => 'SRV-I'.time(), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'registrada', 'diagnostico' => "Revisión\n- Cambio de aceite (150)", 'mano_obra' => 150, 'total' => 150]);

        $this->get("/servicios/{$ot->id}")->assertOk()->assertSee($ot->numero)->assertSee('Cambio de aceite');
        $this->get("/servicios/{$ot->id}/imprimir")->assertOk()->assertSee($ot->numero)->assertSee('Cambio de aceite')->assertSee('Imprimir');
    }

    public function test_imprimir_agrupa_repuestos_e_insumos(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Grupo', 'nit_ci' => 'G-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'GRU-'.random_int(100, 999).substr(microtime(true) * 10000, -4)]);
        $rep = Product::create(['sku' => 'GRP-'.time(), 'nombre' => 'Perno Grupo', 'tipo' => 'repuesto', 'precio_venta' => 10, 'stock' => 5]);
        $ins = Product::create(['sku' => 'GRI-'.time(), 'nombre' => 'Aceite Grupo', 'tipo' => 'insumo', 'precio_venta' => 20, 'stock' => 5]);
        $ot = WorkOrder::create(['numero' => 'SRV-G'.time(), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'registrada', 'mano_obra' => 0, 'total' => 0]);
        $ot->lineas()->create(['product_id' => $rep->id, 'cantidad' => 1, 'precio' => 10]);
        $ot->lineas()->create(['product_id' => $ins->id, 'cantidad' => 1, 'precio' => 20]);
        $html = $this->get("/servicios/{$ot->id}/imprimir")->assertOk()->getContent();
        $this->assertStringContainsString('Repuestos', $html);
        $this->assertStringContainsString('Insumos y materiales', $html);
        $this->assertStringContainsString('Subtotal repuestos', $html);
    }

    public function test_la_mano_de_obra_es_linea_de_servicio_y_la_impresion_separa_por_tipo(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Tipo', 'nit_ci' => 'TP-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'TIP-'.random_int(100, 999).substr(microtime(true) * 10000, -4)]);
        $rep = Product::create(['sku' => 'TPR-'.time(), 'nombre' => 'Perno Tipo', 'tipo' => 'repuesto', 'precio_venta' => 10, 'stock' => 5]);
        $ins = Product::create(['sku' => 'TPI-'.time(), 'nombre' => 'Aceite Tipo', 'tipo' => 'insumo', 'precio_venta' => 20, 'stock' => 5]);
        $ot = app(\App\Application\UseCases\CrearOrdenUseCase::class)->ejecutar(
            $cli->id, $veh->id, 'Mantenimiento', 0.0,
            [['descripcion' => 'Cambio de frenos', 'precio' => 150]],
            [['product_id' => $rep->id, 'cantidad' => 1, 'precio' => 10], ['product_id' => $ins->id, 'cantidad' => 1, 'precio' => 20]]
        );
        // La mano de obra ya no es texto: es una línea de tipo servicio sin producto.
        $servicio = $ot->lineas->firstWhere('tipo', 'servicio');
        $this->assertNotNull($servicio);
        $this->assertNull($servicio->product_id);
        $this->assertEquals('Cambio de frenos', $servicio->descripcion);
        // El tipo de una línea de producto se hereda del producto (no se adivina).
        $this->assertEquals('insumo', $ot->lineas->firstWhere('product_id', $ins->id)->tipo);
        $this->assertEquals('repuesto', $ot->lineas->firstWhere('product_id', $rep->id)->tipo);

        $html = $this->get("/servicios/{$ot->id}/imprimir")->assertOk()->getContent();
        foreach (['Servicios (mano de obra)', 'Cambio de frenos', 'Repuestos', 'Perno Tipo', 'Insumos y materiales', 'Aceite Tipo'] as $t) {
            $this->assertStringContainsString($t, $html);
        }
        // Una sola vez: no se duplica en otra sección.
        $this->assertSame(1, substr_count($html, 'Cambio de frenos'));
    }

    public function test_compra_ver_e_imprimir(): void    {
        $this->actingAs(User::first());
        $sup = Supplier::create(['nombre' => 'Sup Imp']);
        $c = Purchase::create(['folio' => 'CMP-I'.time(), 'supplier_id' => $sup->id, 'tipo_pago' => 'credito', 'total' => 400, 'saldo' => 400]);

        $this->get("/compras/{$c->id}")->assertOk()->assertSee($c->folio);
        $this->get("/compras/{$c->id}/imprimir")->assertOk()->assertSee($c->folio)->assertSee('Imprimir');
    }
}
