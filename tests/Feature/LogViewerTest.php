<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class LogViewerTest extends TestCase
{
    public function test_gate_solo_admin(): void
    {
        $admin = User::role('admin')->first();
        $this->assertTrue(Gate::forUser($admin)->allows('viewLogViewer'));
    }

    public function test_log_viewer_responde_y_demo_escribe_ticket(): void
    {
        $this->actingAs(User::role('admin')->first());
        $this->get('/log-viewer')->assertOk();
        $this->get('/logs/demo')->assertRedirect('/log-viewer');
        $hoy = storage_path('logs/laravel-'.now()->format('Y-m-d').'.log');
        $contenido = file_exists($hoy) ? file_get_contents($hoy) : file_get_contents(storage_path('logs/laravel.log'));
        $this->assertStringContainsString('Demo seguimiento errores ticket=E-', $contenido);
    }
}
