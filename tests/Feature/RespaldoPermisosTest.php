<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RespaldoPermisosTest extends TestCase
{
    public function test_respaldo_crear_descargar_y_clave_mala(): void
    {
        $this->actingAs(User::role('admin')->first());
        $this->post('/respaldos')->assertRedirect('/respaldos');
        $lista = (new \App\Application\Services\RespaldoService)->lista();
        $this->assertNotEmpty($lista);
        $nombre = $lista[0]['nombre'];
        $this->get("/respaldos/{$nombre}/descargar")->assertOk();
        $this->get('/respaldos/malo.zip/descargar')->assertNotFound();
        $this->post("/respaldos/{$nombre}/restaurar", ['confirmacion' => 'RESTAURAR', 'password' => 'mala'])
            ->assertStatus(422);
        $this->post("/respaldos/{$nombre}/restaurar", ['confirmacion' => 'OTRA', 'password' => 'x'])
            ->assertSessionHasErrors('confirmacion');
    }

    public function test_caja_no_abre_usuarios_ni_contabilidad(): void
    {
        $caja = User::create(['name' => 'Caja T', 'email' => 'caja-t@test.bo', 'password' => bcrypt('x')]);
        $caja->assignRole(Role::findByName('caja'));
        $this->actingAs($caja);
        $this->get('/usuarios')->assertForbidden();
        $this->get('/contabilidad/asiento')->assertForbidden();
        $this->get('/config')->assertForbidden();
        $this->get('/clientes')->assertOk();
        $this->get('/servicios')->assertOk();
        $caja->forceDelete();
    }

    public function test_admin_abre_todo(): void
    {
        $this->actingAs(User::role('admin')->first());
        foreach (['/usuarios', '/contabilidad', '/respaldos', '/reportes', '/movimientos'] as $u) {
            $this->get($u)->assertOk();
        }
    }
}
