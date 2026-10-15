<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\ChartAccount;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            'clientes.ver', 'clientes.crear', 'clientes.editar', 'clientes.eliminar',
            'vehiculos.ver', 'vehiculos.crear', 'vehiculos.editar', 'vehiculos.eliminar',
            'productos.ver', 'productos.crear', 'productos.editar', 'productos.eliminar',
            'ots.ver', 'ots.crear', 'ots.cerrar', 'ots.anular',
            'compras.ver', 'compras.crear', 'compras.anular',
            'ventas.ver', 'ventas.crear', 'ventas.anular',
            'facturas.emitir', 'facturas.anular',
            'bancos.ver', 'bancos.mover',
            'contabilidad.ver', 'contabilidad.asiento',
            'reportes.ver', 'config.ver', 'config.editar', 'admin.purge',
        ];
        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $caja = Role::firstOrCreate(['name' => 'caja']);
        $caja->givePermissionTo(['clientes.ver', 'clientes.crear', 'vehiculos.ver', 'productos.ver', 'ots.ver', 'ots.crear', 'ventas.ver', 'ventas.crear', 'facturas.emitir', 'bancos.ver']);

        $mecanico = Role::firstOrCreate(['name' => 'mecanico']);
        $mecanico->givePermissionTo(['ots.ver', 'vehiculos.ver', 'productos.ver']);

        $contador = Role::firstOrCreate(['name' => 'contador']);
        $contador->givePermissionTo(['contabilidad.ver', 'contabilidad.asiento', 'reportes.ver', 'bancos.ver', 'compras.ver']);

        $user = User::firstOrCreate(
            ['email' => 'admin@taller.test'],
            ['name' => 'Administrador', 'password' => bcrypt('admin123')]
        );
        $user->assignRole('admin');

        foreach ([
            ['nombre' => 'Filtros', 'prefijo' => 'FILT', 'tipo' => 'repuesto'],
            ['nombre' => 'Aceites', 'prefijo' => 'ACEI', 'tipo' => 'insumo'],
            ['nombre' => 'Frenos', 'prefijo' => 'FREN', 'tipo' => 'repuesto'],
            ['nombre' => 'Eléctrico', 'prefijo' => 'ELEC', 'tipo' => 'repuesto'],
            ['nombre' => 'Mano de obra', 'prefijo' => 'MDO', 'tipo' => 'servicio'],
        ] as $c) {
            Category::firstOrCreate(['nombre' => $c['nombre']], $c);
        }

        foreach ([
            ['nombre' => 'Caja chica', 'banco' => 'Efectivo', 'numero' => 'CAJA-001', 'moneda' => 'BOB', 'saldo' => 5000],
            ['nombre' => 'Banco Unión CC', 'banco' => 'Banco Unión', 'numero' => '10000001', 'moneda' => 'BOB', 'saldo' => 0],
            ['nombre' => 'Banco BNB', 'banco' => 'BNB', 'numero' => '20000002', 'moneda' => 'BOB', 'saldo' => 0],
            ['nombre' => 'Banco Mercantil', 'banco' => 'Mercantil', 'numero' => '30000003', 'moneda' => 'BOB', 'saldo' => 0],
        ] as $b) {
            // El corte nace aquí (la migración corre antes del seed): inicial = saldo actual.
            BankAccount::firstOrCreate(['numero' => $b['numero']], $b + ['saldo_inicial' => $b['saldo'], 'corte_at' => now()]);
        }
        // Backfill: cuentas viejas sin corte toman su saldo como punto de partida.
        BankAccount::whereNull('corte_at')->get()->each(fn ($c) => $c->update(['saldo_inicial' => $c->saldo, 'corte_at' => now()]));

        foreach ([
            ['codigo' => '1', 'nombre' => 'ACTIVO', 'tipo' => 'activo', 'nivel' => 1, 'imputable' => false],
            ['codigo' => '1.1', 'nombre' => 'Disponible', 'tipo' => 'activo', 'nivel' => 2, 'imputable' => false],
            ['codigo' => '1.1.01', 'nombre' => 'Caja', 'tipo' => 'activo', 'nivel' => 3, 'imputable' => true],
            ['codigo' => '1.1.02', 'nombre' => 'Bancos', 'tipo' => 'activo', 'nivel' => 3, 'imputable' => true],
            ['codigo' => '1.2', 'nombre' => 'Inventario', 'tipo' => 'activo', 'nivel' => 2, 'imputable' => true],
            ['codigo' => '2', 'nombre' => 'PASIVO', 'tipo' => 'pasivo', 'nivel' => 1, 'imputable' => false],
            ['codigo' => '2.1', 'nombre' => 'Cuentas por pagar', 'tipo' => 'pasivo', 'nivel' => 2, 'imputable' => true],
            ['codigo' => '4', 'nombre' => 'INGRESOS', 'tipo' => 'ingreso', 'nivel' => 1, 'imputable' => false],
            ['codigo' => '4.1', 'nombre' => 'Ventas repuestos', 'tipo' => 'ingreso', 'nivel' => 2, 'imputable' => true],
            ['codigo' => '4.2', 'nombre' => 'Servicios taller', 'tipo' => 'ingreso', 'nivel' => 2, 'imputable' => true],
            ['codigo' => '5', 'nombre' => 'COSTOS Y GASTOS', 'tipo' => 'gasto', 'nivel' => 1, 'imputable' => false],
            ['codigo' => '5.1', 'nombre' => 'Costo de ventas', 'tipo' => 'gasto', 'nivel' => 2, 'imputable' => true],
            ['codigo' => '5.2', 'nombre' => 'Gastos operativos', 'tipo' => 'gasto', 'nivel' => 2, 'imputable' => true],
        ] as $a) {
            ChartAccount::firstOrCreate(['codigo' => $a['codigo']], $a);
        }
    }
}
