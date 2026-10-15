<?php

namespace Tests\Feature;

use App\Domain\Pagos\Credito;
use App\Domain\Pagos\Efectivo;
use App\Domain\Pagos\FabricaMedios;
use App\Domain\Pagos\Transferencia;
use Tests\TestCase;

/** Blindaje SOLID/limpia: la UI no persiste, las rutas no calculan. */
class ArquitecturaTest extends TestCase
{
    public function test_ui_no_escribe_en_base(): void
    {
        $prohibido = ['DB::', '::create(', 'updateOrCreate', '->update(', '->delete()', '->restore()', '->increment(', '->decrement(', 'app('];
        $mal = [];
        foreach (glob(app_path('Livewire/*.php')) as $f) {
            $lineas = file($f, FILE_IGNORE_NEW_LINES);
            foreach ($lineas as $n => $l) {
                if (str_starts_with(trim($l), '*') || str_starts_with(trim($l), '//')) continue;
                foreach ($prohibido as $p) {
                    if (str_contains($l, $p)) $mal[] = basename($f).':'.($n + 1).' '.$p;
                }
            }
        }
        $this->assertEmpty($mal, 'Escrituras en UI: '.implode(', ', $mal));
    }

    public function test_rutas_no_calculan(): void
    {
        $rutas = file_get_contents(base_path('routes/web.php'));
        foreach (['selectRaw', '::sum(', '->count()', 'groupBy', 'DB::'] as $p) {
            $this->assertStringNotContainsString($p, $rutas, "Lógica en rutas: {$p}");
        }
    }

    public function test_medios_abiertos_a_extension(): void
    {
        $this->assertInstanceOf(Efectivo::class, FabricaMedios::crear('efectivo'));
        $this->assertInstanceOf(Transferencia::class, FabricaMedios::crear('transferencia'));
        $this->assertInstanceOf(Credito::class, FabricaMedios::crear('credito'));
        $this->expectException(\InvalidArgumentException::class);
        FabricaMedios::crear('trueque');
    }

    public function test_casos_de_uso_cubren_operaciones(): void
    {
        foreach (['CrearOrdenUseCase', 'EditarOrdenUseCase', 'CobrarOrdenUseCase', 'AbonarOrdenUseCase', 'AnularOrdenUseCase', 'RegistrarCompraUseCase', 'AnularCompraUseCase', 'AbonarCompraUseCase', 'RegistrarMovimientoUseCase', 'GuardarClienteUseCase', 'GuardarProductoUseCase', 'GuardarVehiculoUseCase', 'ArchivarUseCase'] as $uc) {
            $this->assertTrue(class_exists("App\\Application\\UseCases\\{$uc}"), $uc);
        }
    }
}
