<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class NavbarTest extends TestCase
{
    public function test_navbar_con_usuario_y_campana(): void
    {
        $u = User::first();
        $this->actingAs($u);
        $html = $this->get('/dashboard')->assertOk()->getContent();
        $this->assertStringContainsString('Notificaciones', $html);
        $this->assertStringContainsString('/logout', $html);
        $this->assertStringContainsString(e($u->name), $html);
        $this->assertStringNotContainsString('border-t border-slate-700', $html);
    }
}
