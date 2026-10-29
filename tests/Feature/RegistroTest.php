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
        $cat = \App\Models\Category::firstOrCreate(['nombre' => 'Filtros T'], ['prefijo' => 'FLT', 'tipo' => 'repuesto']);
        Livewire::test('product-form')
            ->set('nombre', 'Filtro Prueba T')
            ->set('category_id', $cat->id)
            ->set('precio_venta', 120)
            ->call('guardar')
            ->assertRedirect('/productos');
        $this->assertDatabaseHas('products', ['nombre' => 'Filtro Prueba T', 'tipo' => 'repuesto']);
    }

    public function test_producto_nace_sin_stock_y_editar_no_lo_toca(): void
    {
        $this->actingAs(User::first());
        $cat = \App\Models\Category::firstOrCreate(['nombre' => 'Filtros T'], ['prefijo' => 'FLT', 'tipo' => 'repuesto']);
        $suf = time().random_int(100, 999);
        Livewire::test('product-form')
            ->set('nombre', 'Filtro Sin Stock T'.$suf)
            ->set('category_id', $cat->id)
            ->set('precio_venta', 120)
            ->call('guardar')
            ->assertRedirect('/productos');
        $p = \App\Models\Product::where('nombre', 'Filtro Sin Stock T'.$suf)->firstOrFail();
        $this->assertEquals(0, (float) $p->stock);
        Livewire::test('product-form', ['id' => $p->id])
            ->set('precio_venta', 150)
            ->call('guardar')
            ->assertRedirect('/productos');
        $this->assertEquals(0, (float) $p->fresh()->stock);
    }

    public function test_paginas_crear_responden(): void
    {
        $this->actingAs(User::first());
        foreach (['/clientes/crear', '/productos/crear', '/servicios/crear', '/compras/crear', '/categorias'] as $u) {
            $this->get($u)->assertOk();
        }
    }
}
