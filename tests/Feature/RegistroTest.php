<?php

namespace Tests\Feature;

use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class RegistroTest extends TestCase
{
    public function test_cliente_guarda(): void
    {
        $this->actingAs(User::first());
        Livewire::test('client-form')
            ->set('nombre', 'Cliente Prueba T')
            ->set('nit_ci', 'TEST-'.time())
            ->call('guardar')
            ->assertRedirect('/clientes');
        $this->assertDatabaseHas('clients', ['nombre' => 'Cliente Prueba T']);
    }

    public function test_producto_guarda_con_sku(): void
    {
        $this->actingAs(User::first());
        Livewire::test('product-form')
            ->set('nombre', 'Filtro Prueba T')
            ->set('tipo', 'repuesto')
            ->set('precio_venta', 120)
            ->call('guardar')
            ->assertRedirect('/productos');
        $this->assertDatabaseHas('products', ['nombre' => 'Filtro Prueba T']);
    }

    public function test_paginas_crear_responden(): void
    {
        $this->actingAs(User::first());
        foreach (['/clientes/crear', '/productos/crear', '/servicios/crear', '/compras/crear', '/categorias'] as $u) {
            $this->get($u)->assertOk();
        }
    }
}
