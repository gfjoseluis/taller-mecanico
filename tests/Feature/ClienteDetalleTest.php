<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;
use Tests\TestCase;

class ClienteDetalleTest extends TestCase
{
    public function test_cliente_con_vehiculos_en_un_registro(): void
    {
        $this->actingAs(User::first());
        $placa = 'ADD-'.random_int(1000, 9999).substr(time(), -3);
        $nom = 'Cliente Detalle '.time();
        Livewire::test('client-form')
            ->set('nombre', $nom)
            ->set('nit_ci', 'DET-'.time().random_int(10, 99))
            ->call('addVehiculo')
            ->set('vehiculos.0.placa', $placa)
            ->set('vehiculos.0.marca', 'Volvo')
            ->set('vehiculos.0.modelo', 'FH')
            ->call('guardar')
            ->assertRedirect('/clientes');
        $c = Client::where('nombre', $nom)->firstOrFail();
        $this->assertEquals($placa, $c->vehicles()->first()->placa);
    }

    public function test_modal_cliente_servicio_guarda_campos_completos(): void
    {
        $this->actingAs(User::first());
        $nit = 'MOD-'.time();
        Livewire::test('servicio-form')
            ->set('c_nombre', 'Modal Completo')
            ->set('c_nit_ci', $nit)
            ->set('c_telefono', '70000001')
            ->set('c_email', 'modal@taller.bo')
            ->set('c_direccion', 'Av. Test 123')
            ->call('guardarCliente');
        $c = Client::where('nit_ci', $nit)->firstOrFail();
        $this->assertEquals('modal@taller.bo', $c->email);
        $this->assertEquals('Av. Test 123', $c->direccion);
    }

    public function test_vistas_tienen_labels_en_lineas(): void
    {
        $src = file_get_contents(resource_path('views/livewire/servicio-form.blade.php'));
        $this->assertStringContainsString('Repuesto / Insumo*', $src);
        $this->assertStringContainsString('Subtotal', $src);
        $cli = file_get_contents(resource_path('views/livewire/client-form.blade.php'));
        $this->assertStringContainsString('add vehicle', strtolower($cli));
        $this->assertStringContainsString('components.client-fields', file_get_contents(resource_path('views/livewire/servicio-form.blade.php')));
    }
}
