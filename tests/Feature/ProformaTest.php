<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;
use Tests\TestCase;

/** Pro-forma: presupuesta con precios sin tocar stock ni dinero. */
class ProformaTest extends TestCase
{
    public function test_proforma_no_mueve_stock_ni_dinero(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Pro', 'nit_ci' => 'PR-'.time().random_int(100, 999)]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'PRO-'.random_int(100, 999).substr(microtime(true) * 10000, -4)]);
        $prod = Product::create(['sku' => 'PRF-'.time().random_int(100, 999), 'nombre' => 'Filtro Pro', 'tipo' => 'repuesto', 'precio_venta' => 120, 'stock' => 5]);
        Livewire::test('proforma-form')
            ->set('client_id', $cli->id)->set('vehicle_id', $veh->id)
            ->set('diagnostico', 'Cotización')
            ->set('servicios', [['descripcion' => 'Mano', 'precio' => 100]])
            ->set('lineas', [['product_id' => $prod->id, 'cantidad' => 2, 'precio' => 120]])
            ->set('descuento', 40)
            ->call('guardar')->assertRedirect('/proformas');
        $p = \App\Models\Proforma::latest()->firstOrFail();
        $this->assertEquals(300, (float) $p->total);
        $this->assertEquals('pendiente', $p->estado);
        $this->assertEquals(5, (float) $prod->fresh()->stock); // intacto
        $this->get("/proformas/{$p->id}")->assertOk()->assertSee($p->numero);
        $this->get("/proformas/{$p->id}/imprimir")->assertOk()->assertSee('PRO-FORMA')->assertSee('Son:');
    }

    public function test_convertir_crea_ot_y_descuenta_stock(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Conv', 'nit_ci' => 'PC-'.time().random_int(100, 999)]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'CNV-'.random_int(100, 999).substr(microtime(true) * 10000, -4)]);
        $prod = Product::create(['sku' => 'CNV-'.time().random_int(100, 999), 'nombre' => 'Filtro Conv', 'tipo' => 'repuesto', 'precio_venta' => 120, 'stock' => 5]);
        $p = app(\App\Application\UseCases\GuardarProformaUseCase::class)->ejecutar(
            null, $cli->id, $veh->id, 'Cotización', 0.0,
            [['descripcion' => 'Mano', 'precio' => 100]],
            [['product_id' => $prod->id, 'cantidad' => 2, 'precio' => 120]], 40
        );
        $this->post("/proformas/{$p->id}/convertir")->assertStatus(302);
        $ot = $p->fresh()->orden;
        $this->assertNotNull($ot);
        $this->assertEquals('registrada', $ot->estado);
        $this->assertEquals(300, (float) $ot->total);
        $this->assertEquals(3, (float) $prod->fresh()->stock);
        $this->assertEquals('aceptada', $p->fresh()->estado);
        // Doble conversión se bloquea.
        $this->post("/proformas/{$p->id}/convertir")->assertRedirect('/proformas');
        $this->assertEquals('aceptada', $p->fresh()->estado);
    }

    public function test_recepcion_guarda_y_se_muestra(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Rec', 'nit_ci' => 'RC-'.time().random_int(100, 999)]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'REC-'.random_int(100, 999).substr(microtime(true) * 10000, -4)]);
        $ot = \App\Models\WorkOrder::create(['numero' => 'SRV-R'.time().random_int(100, 999), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'registrada', 'total' => 0]);
        Livewire::test('recepcion-form', ['id' => $ot->id])
            ->set('kilometraje', 71158)
            ->set('combustible', 'lleno')
            ->set('inventario', ['gato', 'extintor'])
            ->set('inspeccion', ['motor', 'frenos'])
            ->set('observaciones', 'Rayón puerta')
            ->call('guardar')->assertRedirect("/servicios/{$ot->id}");
        $r = $ot->fresh()->recepcion;
        $this->assertEquals(71158, $r->kilometraje);
        $this->assertEquals('lleno', $r->combustible);
        $this->assertEquals(['gato', 'extintor'], $r->inventario);
        $this->get("/servicios/{$ot->id}")->assertOk()->assertSee('Rayón puerta')->assertSee('Km: '.number_format(71158, 0));
        $this->get("/servicios/{$ot->id}/imprimir")->assertOk()->assertSee('Recepción')->assertSee('Km: '.number_format(71158, 0));
        $html = $this->get("/servicios/{$ot->id}/recepcion")->assertOk()->getContent();
        foreach (['Vacío', '1/4', '1/2', '3/4', 'Lleno'] as $nivel) $this->assertStringContainsString($nivel, $html);
    }

    public function test_foto_se_normaliza_y_guarda(): void
    {
        $this->actingAs(User::first());
        $cli = Client::create(['nombre' => 'Cli Foto', 'nit_ci' => 'FT-'.time().random_int(100, 999)]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'FOT-'.random_int(100, 999).substr(microtime(true) * 10000, -4)]);
        $ot = \App\Models\WorkOrder::create(['numero' => 'SRV-F'.time().random_int(100, 999), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'registrada', 'total' => 0]);
        $foto = \Illuminate\Http\UploadedFile::fake()->image('arriba.jpg', 2000, 1500);
        Livewire::test('recepcion-form', ['id' => $ot->id])
            ->set('subidas.arriba', $foto)
            ->call('procesarFoto', 'arriba')
            ->assertHasNoErrors();
        $f = \App\Models\RecepcionFoto::where('posicion', 'arriba')->latest()->firstOrFail();
        $ruta = storage_path('app/public/'.$f->ruta);
        $this->assertFileExists($ruta);
        [$ancho] = getimagesize($ruta);
        $this->assertLessThanOrEqual(1280, $ancho);
        $this->assertEquals('image/jpeg', mime_content_type($ruta));
    }
}
