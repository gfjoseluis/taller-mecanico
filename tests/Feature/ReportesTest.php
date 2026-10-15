<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ReportesTest extends TestCase
{
    public function test_dashboard_con_graficos(): void
    {
        $html = $this->actingAs(User::first())->get('/dashboard')->assertOk()->getContent();
        $this->assertStringContainsString('chVentas', $html);
        $this->assertStringContainsString('chEstados', $html);
        $this->assertStringContainsString('chart.umd.min.js', $html);
    }

    public function test_reportes_y_csv(): void
    {
        $this->actingAs(User::first());
        foreach (['ventas', 'top', 'mecanicos', 'ganancia', 'bancos'] as $s) {
            $this->get("/reportes/{$s}")->assertOk();
        }
        $this->get('/reportes/ventas?csv=1')->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
