<?php

namespace Tests\Feature;

use App\Application\Services\MovimientoService;
use App\Models\BankAccount;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

/** Ninguna cuenta puede quedar en negativo: la salida se bloquea con mensaje claro. */
class SaldoInsuficienteTest extends TestCase
{
    private function cuentaVacia(): BankAccount
    {
        return BankAccount::create(['nombre' => 'T-Vacía', 'banco' => 'T', 'numero' => 'TV-'.microtime(true).random_int(1000, 9999), 'saldo' => 0, 'saldo_inicial' => 0, 'corte_at' => now()]);
    }

    public function test_salida_y_traspaso_sin_dinero_fallan(): void
    {
        $this->actingAs(User::first());
        $mov = app(MovimientoService::class);
        $vacia = $this->cuentaVacia();
        $dest = BankAccount::create(['nombre' => 'T-Dest', 'banco' => 'T', 'numero' => 'TD-'.time().random_int(100, 999), 'saldo' => 0, 'saldo_inicial' => 0, 'corte_at' => now()]);

        foreach ([
            ['tipo' => 'salida', 'cuenta_id' => $vacia->id, 'monto' => 1000, 'motivo' => 'Pago test'],
            ['tipo' => 'traspaso', 'cuenta_id' => $vacia->id, 'cuenta_destino_id' => $dest->id, 'monto' => 500, 'motivo' => 'Traspaso test'],
        ] as $d) {
            try {
                $mov->registrar($d);
                $this->fail('Debió bloquearse: '.$d['tipo']);
            } catch (\DomainException $e) {
                $this->assertStringContainsString('Sin dinero suficiente', $e->getMessage());
            }
        }
        $this->assertEquals(0, (float) $vacia->fresh()->saldo);
    }

    public function test_compra_contado_sin_dinero_muestra_error(): void
    {
        $this->actingAs(User::first());
        $vacia = $this->cuentaVacia();
        $sup = Supplier::create(['nombre' => 'Sup Vacio '.time().random_int(100, 999)]);
        $prod = Product::create(['sku' => 'VAC-'.time().random_int(100, 999), 'nombre' => 'Rep Vacio', 'tipo' => 'repuesto', 'precio_venta' => 100, 'stock' => 0]);
        Livewire::test('compra-form')
            ->set('supplier_id', $sup->id)->set('bank_account_id', $vacia->id)
            ->set('lineas', [['product_id' => $prod->id, 'cantidad' => 2, 'costo' => 500]])
            ->call('guardar')->assertHasErrors(['bank_account_id']);
        $this->assertEquals(0, Purchase::where('supplier_id', $sup->id)->count());
    }

    public function test_entrada_siempre_pasa(): void
    {
        $this->actingAs(User::first());
        $vacia = $this->cuentaVacia();
        app(MovimientoService::class)->registrar(['tipo' => 'entrada', 'cuenta_id' => $vacia->id, 'monto' => 250, 'motivo' => 'Entrada test']);
        $this->assertEquals(250, (float) $vacia->fresh()->saldo);
    }

    public function test_compra_local_sin_proveedor_va_a_generico(): void
    {
        $this->actingAs(User::first());
        $prod = Product::create(['sku' => 'LOC-'.time().random_int(100, 999), 'nombre' => 'Rep Local', 'tipo' => 'repuesto', 'precio_venta' => 100, 'stock' => 0]);
        Livewire::test('compra-form')
            ->set('tipo_pago', 'contado')
            ->set('glosa', 'Ferretería El Puente')
            ->set('lineas', [['product_id' => $prod->id, 'cantidad' => 1, 'costo' => 50]])
            ->call('guardar')->assertRedirect('/compras');
        $c = Purchase::latest()->first();
        $this->assertEquals('Compra local / mostrador', $c->supplier->nombre);
        $this->assertEquals(1, (float) $prod->fresh()->stock);
    }

    public function test_compra_credito_sin_proveedor_falla(): void
    {
        $this->actingAs(User::first());
        $prod = Product::create(['sku' => 'LCC-'.time().random_int(100, 999), 'nombre' => 'Rep Local Cred '.time(), 'tipo' => 'repuesto', 'precio_venta' => 100, 'stock' => 0]);
        Livewire::test('compra-form')
            ->set('tipo_pago', 'credito')
            ->set('glosa', 'Sin dueño')
            ->set('lineas', [['product_id' => $prod->id, 'cantidad' => 1, 'costo' => 50]])
            ->call('guardar')->assertHasErrors(['supplier_id']);
    }

    public function test_editar_cuenta_no_toca_dinero(): void
    {
        $this->actingAs(User::first());
        $b = BankAccount::create(['nombre' => 'T-Fija', 'banco' => 'T', 'numero' => 'TF-'.time(), 'saldo' => 1000, 'saldo_inicial' => 1000, 'corte_at' => now()]);
        Livewire::test('bank-form', ['id' => $b->id])
            ->set('nombre', 'T-Fija Nueva')->set('saldo', 99999)
            ->call('guardar')->assertRedirect('/bancos');
        $this->assertEquals(1000, (float) $b->fresh()->saldo);
        $this->assertEquals('T-Fija Nueva', $b->fresh()->nombre);
    }
}
