<?php

namespace Tests\Feature;

use App\Application\Services\NotificacionService;
use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Tests\TestCase;

class NotificacionesTest extends TestCase
{
    public function test_los_5_avisos(): void
    {
        $admin = User::role('admin')->first();
        $this->actingAs($admin);
        \Illuminate\Support\Facades\Cache::flush();
        $prod = Product::create(['sku' => 'NTF-'.time(), 'nombre' => 'Notif T', 'tipo' => 'repuesto', 'precio_venta' => 10, 'stock' => 0, 'stock_minimo' => 5]);
        $cli = Client::create(['nombre' => 'Cli Ntf', 'nit_ci' => 'NT-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'NTF-'.random_int(100, 999).substr(time(), -4)]);
        $ot = WorkOrder::create(['numero' => 'SRV-N'.time(), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'registrada', 'total' => 100]);
        $ot->created_at = now()->subDays(8); $ot->save();
        $a = app(NotificacionService::class)->avisos();
        $titulos = collect($a['lista'])->pluck('titulo')->join(' | ');
        $urls = collect($a['lista'])->pluck('url')->join(' | ');
        $this->assertStringContainsString('stock bajo', $titulos);
        $this->assertStringContainsString('+7 días abiertas', $titulos);
        $this->assertStringContainsString('sigue abierto', $titulos);
        $this->assertStringContainsString('/productos?solo_bajo=1', $urls);
        $this->assertStringContainsString('/servicios?antiguas=1', $urls);
        $this->assertGreaterThanOrEqual(3, $a['conteo']);
        $html = $this->get('/dashboard')->assertOk()->getContent();
        $this->assertStringContainsString('Notificaciones (', $html);
        // Cada aviso abre su lista ya filtrada.
        $this->get('/productos?solo_bajo=1')->assertOk()->assertSee($prod->sku);
        $this->get('/servicios?antiguas=1')->assertOk()->assertSee($ot->numero);
        $prod->forceDelete();
        $ot->forceDelete();
        $veh->forceDelete();
        $cli->forceDelete();
    }

    public function test_mora_filtra_deudas_viejas(): void
    {
        $admin = User::role('admin')->first();
        $this->actingAs($admin);
        $cli = Client::create(['nombre' => 'Cli Mora', 'nit_ci' => 'MO-'.time()]);
        $veh = Vehicle::create(['client_id' => $cli->id, 'placa' => 'MOR-'.random_int(100, 999).substr(time(), -4)]);
        $vieja = WorkOrder::create(['numero' => 'SRV-M'.time(), 'client_id' => $cli->id, 'vehicle_id' => $veh->id, 'estado' => 'cerrada-pendiente', 'total' => 100]);
        $vieja->cerrada_at = now()->subDays(20); $vieja->save();
        $this->get('/por-cobrar?mora=1')->assertOk()->assertSee($vieja->numero);
        $vieja->forceDelete(); $veh->forceDelete(); $cli->forceDelete();
    }

    public function test_ultimo_respaldo_devuelve_fecha_o_nulo(): void
    {
        $this->actingAs(User::role('admin')->first());
        $u = app(\App\Application\Services\RespaldoService::class)->ultimo();
        $this->assertTrue($u === null || $u instanceof \Carbon\Carbon);
    }
}
