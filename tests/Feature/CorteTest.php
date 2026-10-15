<?php

namespace Tests\Feature;

use App\Application\Services\MovimientoService;
use App\Models\BankAccount;
use Tests\TestCase;

class CorteTest extends TestCase
{
    public function test_seed_fresco_cuadra(): void
    {
        $this->actingAs(\App\Models\User::first());
        $mov = app(MovimientoService::class);
        foreach (BankAccount::all() as $b) {
            $this->assertNotNull($b->corte_at, $b->nombre);
            $this->assertNotNull($b->saldo_inicial, $b->nombre);
            $this->assertEqualsWithDelta((float) $b->saldo, $mov->saldoCalculado($b), 0.01, $b->nombre);
        }
        $this->get('/bancos')->assertOk()->assertSee('✓ cuadra', false);
    }
}
