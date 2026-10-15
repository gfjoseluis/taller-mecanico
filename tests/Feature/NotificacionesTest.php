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
        $ot->created_at = now()->subDays(5); $ot->save();
        $a = app(NotificacionService::class)->avisos();
        $titulos = collect($a['lista'])->pluck('titulo')->join(' | ');
        $this->assertStringContainsString('stock bajo', $titulos);
        $this->assertStringContainsString('+3 días abiertas', $titulos);
        $this->assertStringContainsString('sigue abierto', $titulos);
        $this->assertGreaterThanOrEqual(3, $a['conteo']);
        $html = $this->get('/dashboard')->assertOk()->getContent();
        $this->assertStringContainsString('Notificaciones (', $html);
        $prod->forceDelete();
        $ot->forceDelete();
        $veh->forceDelete();
        $cli->forceDelete();
    }
}
