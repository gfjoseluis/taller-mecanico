<?php

use App\Http\Controllers\AuthController;
use App\Models\BankAccount;
use App\Models\Invoice;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect('/dashboard'));
    Route::get('/dashboard', fn (\App\Application\Lecturas\TableroService $t) => view('dashboard', $t->datos()))->name('dashboard');

    $listas = [
        'clientes' => ['client-table', 'clientes.ver'], 'vehiculos' => ['vehicle-table', 'vehiculos.ver'],
        'productos' => ['product-table', 'productos.ver'], 'categorias' => ['category-table', 'productos.ver'],
        'proveedores' => ['supplier-table', 'compras.ver'], 'servicios' => ['servicio-table', 'ots.ver'],
        'compras' => ['compra-table', 'compras.ver'], 'usuarios' => ['user-table', 'config.editar'],
        'roles' => ['role-table', 'config.editar'], 'movimientos' => ['movimiento-table', 'bancos.ver'],
        'socios' => ['partner-table', 'bancos.ver'], 'config' => ['settings-form', 'config.ver'],
    ];
    foreach ($listas as $uri => [$comp, $perm]) {
        Route::get('/'.$uri, fn () => view('page', ['comp' => $comp]))->middleware("can:{$perm}")->name($uri);
    }

    Route::get('/clientes/crear', fn () => view('form-page', ['comp' => 'client-form', 'titulo' => 'Nuevo cliente']))->middleware('can:clientes.crear');
    Route::get('/clientes/{id}/editar', fn ($id) => view('form-page', ['comp' => 'client-form', 'titulo' => 'Editar cliente', 'id' => $id]))->middleware('can:clientes.editar');
    Route::get('/clientes/{id}', fn ($id) => view('form-page', ['comp' => 'client-show', 'titulo' => 'Cliente y vehículos', 'id' => $id]))->middleware('can:clientes.ver')->name('clientes.show');
    Route::get('/vehiculos/crear', fn () => view('form-page', ['comp' => 'vehicle-form', 'titulo' => 'Nuevo vehículo']))->middleware('can:vehiculos.crear');
    Route::get('/vehiculos/{id}/editar', fn ($id) => view('form-page', ['comp' => 'vehicle-form', 'titulo' => 'Editar vehículo', 'id' => $id]))->middleware('can:vehiculos.editar');
    Route::get('/productos/crear', fn () => view('form-page', ['comp' => 'product-form', 'titulo' => 'Nuevo producto']))->middleware('can:productos.crear');
    Route::get('/productos/{id}/editar', fn ($id) => view('form-page', ['comp' => 'product-form', 'titulo' => 'Editar producto', 'id' => $id]))->middleware('can:productos.editar');
    Route::get('/categorias/crear', fn () => view('form-page', ['comp' => 'category-form', 'titulo' => 'Nueva categoría']))->middleware('can:productos.crear');
    Route::get('/categorias/{id}/editar', fn ($id) => view('form-page', ['comp' => 'category-form', 'titulo' => 'Editar categoría', 'id' => $id]))->middleware('can:productos.editar');
    Route::get('/proveedores/crear', fn () => view('form-page', ['comp' => 'supplier-form', 'titulo' => 'Nuevo proveedor']))->middleware('can:compras.crear');
    Route::get('/proveedores/{id}/editar', fn ($id) => view('form-page', ['comp' => 'supplier-form', 'titulo' => 'Editar proveedor', 'id' => $id]))->middleware('can:compras.crear');
    Route::get('/bancos/crear', fn () => view('form-page', ['comp' => 'bank-form', 'titulo' => 'Nueva cuenta']))->middleware('can:bancos.mover');
    Route::get('/bancos/{id}/editar', fn ($id) => view('form-page', ['comp' => 'bank-form', 'titulo' => 'Editar cuenta', 'id' => $id]))->middleware('can:bancos.mover');
    Route::get('/servicios/crear', fn () => view('form-page', ['comp' => 'servicio-form', 'titulo' => 'Nueva orden de servicio']))->middleware('can:ots.crear');
    Route::get('/proformas', fn () => view('page', ['comp' => 'proforma-table']))->middleware('can:ots.ver')->name('proformas');
    Route::get('/proformas/crear', fn () => view('form-page', ['comp' => 'proforma-form', 'titulo' => 'Nueva pro-forma']))->middleware('can:ots.crear');
    Route::get('/proformas/{id}/editar', fn ($id) => view('form-page', ['comp' => 'proforma-form', 'titulo' => 'Editar pro-forma', 'id' => $id]))->middleware('can:ots.crear');
    Route::post('/proformas/{id}/convertir', function (int $id, \App\Application\UseCases\ConvertirProformaUseCase $conv) {
        try {
            $ot = $conv->ejecutar($id);
        } catch (\DomainException $e) {
            return redirect('/proformas')->with('ok', $e->getMessage());
        }
        return redirect('/servicios/'.$ot->id)->with('ok', "OT {$ot->numero} creada desde pro-forma (stock descontado).");
    })->middleware('can:ots.crear');
    Route::get('/proformas/{id}', fn ($id) => view('form-page', ['comp' => 'proforma-show', 'titulo' => 'Detalle de pro-forma', 'id' => $id]))->middleware('can:ots.ver')->whereNumber('id');
    Route::get('/proformas/{id}/imprimir', function (int $id, \App\Application\Lecturas\OrdenDetalleService $d, \App\Application\Services\SettingService $s) {
        $p = \App\Models\Proforma::with(['client', 'vehicle', 'lineas.product'])->findOrFail($id);
        return view('print.proforma', ['p' => $p, 'empresa' => $s->get('taller.empresa.nombre', 'Taller Alto Tonelaje'), 'diagnosticoBase' => $d->diagnosticoBase($p->diagnostico), 'grupos' => $d->gruposDe($p)]);
    })->middleware('can:ots.ver');
    Route::get('/servicios/{id}/editar', fn ($id) => view('form-page', ['comp' => 'servicio-form', 'titulo' => 'Editar orden (abierta)', 'id' => $id]))->middleware('can:ots.crear');
    Route::get('/servicios/{id}/cobrar', fn ($id) => view('form-page', ['comp' => 'cobro-form', 'titulo' => 'Cobrar', 'id' => $id]))->middleware('can:ots.cerrar');
    Route::get('/servicios/{id}/recepcion', fn ($id) => view('form-page', ['comp' => 'recepcion-form', 'titulo' => 'Recepción del vehículo', 'id' => $id]))->middleware('can:ots.crear');
    Route::get('/servicios/{id}', fn ($id) => view('form-page', ['comp' => 'servicio-show', 'titulo' => 'Detalle de orden', 'id' => $id]))->middleware('can:ots.ver')->whereNumber('id');
    Route::get('/compras/crear', fn () => view('form-page', ['comp' => 'compra-form', 'titulo' => 'Nueva compra']))->middleware('can:compras.crear');
    Route::get('/compras/{id}', fn ($id) => view('form-page', ['comp' => 'compra-show', 'titulo' => 'Detalle de compra', 'id' => $id]))->middleware('can:compras.ver')->whereNumber('id');
    Route::get('/compras/{id}/imprimir', function (int $id, \App\Application\Lecturas\CompraDetalleService $d, \App\Application\Services\SettingService $s) {
        return view('print.compra', ['c' => $d->cargar($id), 'empresa' => $s->get('taller.empresa.nombre', 'Taller Alto Tonelaje')]);
    })->middleware('can:compras.ver');
    Route::get('/usuarios/crear', fn () => view('form-page', ['comp' => 'user-form', 'titulo' => 'Nuevo usuario']))->middleware('can:config.editar');    Route::get('/usuarios/{id}/editar', fn ($id) => view('form-page', ['comp' => 'user-form', 'titulo' => 'Editar usuario', 'id' => $id]))->middleware('can:config.editar');
    Route::get('/roles/crear', fn () => view('form-page', ['comp' => 'role-form', 'titulo' => 'Nuevo rol']))->middleware('can:config.editar');
    Route::get('/roles/{id}/editar', fn ($id) => view('form-page', ['comp' => 'role-form', 'titulo' => 'Editar rol', 'id' => $id]))->middleware('can:config.editar');
    Route::get('/movimientos/crear', fn () => view('form-page', ['comp' => 'movimiento-form', 'titulo' => 'Nuevo movimiento']))->middleware('can:bancos.mover');
    Route::get('/socios/crear', fn () => view('form-page', ['comp' => 'partner-form', 'titulo' => 'Nuevo socio']))->middleware('can:bancos.mover');
    Route::get('/socios/{id}/editar', fn ($id) => view('form-page', ['comp' => 'partner-form', 'titulo' => 'Editar socio', 'id' => $id]))->middleware('can:bancos.mover');

    Route::redirect('/ots', '/servicios');
    Route::redirect('/ots/crear', '/servicios/crear');

    Route::get('/bancos', function (\App\Application\Services\MovimientoService $mov) {
        $cuentas = BankAccount::all()->each(fn ($b) => $b->calculado = $mov->saldoCalculado($b));
        return view('bancos', ['cuentas' => $cuentas]);
    })->middleware('can:bancos.ver')->name('bancos');
    Route::get('/contabilidad', function (\App\Application\Lecturas\ContabilidadService $c) {
        return view('contabilidad', ['asientos' => $c->diario(request('desde'), request('hasta'))]);
    })->middleware('can:contabilidad.ver')->name('contabilidad');
    Route::get('/contabilidad/mayor', function (\App\Application\Lecturas\ContabilidadService $c) {
        return view('mayor', $c->mayor(request('cuenta') ? (int) request('cuenta') : null));
    })->middleware('can:contabilidad.ver');
    Route::get('/contabilidad/balance', function (\App\Application\Lecturas\ContabilidadService $c) {
        $b = $c->balance();
        return view('balance', ['filas' => $b['filas'], 'titulo' => 'Balance general (lo que tienes vs. lo que debes)', 'nota' => $b['nota']]);
    })->middleware('can:contabilidad.ver')->name('contabilidad.balance');
    Route::get('/contabilidad/resultados', function (\App\Application\Lecturas\ContabilidadService $c) {
        $b = $c->resultados();
        return view('balance', ['filas' => $b['filas'], 'titulo' => 'Ganancias y pérdidas', 'nota' => $b['nota']]);
    })->middleware('can:contabilidad.ver');
    Route::get('/contabilidad/balance/csv', function (\App\Application\Lecturas\ContabilidadService $c) {
        $filas = $c->filasBalance();
        return response()->streamDownload(function () use ($filas) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['cuenta', 'nombre', 'debe', 'haber']);
            foreach ($filas as $r) fputcsv($f, $r);
            fclose($f);
        }, 'balance.csv', ['Content-Type' => 'text/csv']);
    })->middleware('can:contabilidad.ver');
    Route::get('/contabilidad/equivalencias', fn () => view('form-page', ['comp' => 'equivalencias-form', 'titulo' => 'Equivalencias contables']))->middleware('can:contabilidad.asiento');
    Route::get('/contabilidad/asiento', fn () => view('form-page', ['comp' => 'asiento-form', 'titulo' => 'Registro manual']))->middleware('can:contabilidad.asiento');
    Route::get('/contabilidad/cierre', fn () => view('cierre', ['periodos' => \App\Models\Periodo::orderBy('gestion')->orderBy('mes')->get()]))->middleware('can:contabilidad.ver');
    Route::post('/contabilidad/cierre', function () {
        $d = request()->validate(['gestion' => 'required|integer', 'mes' => 'required|integer|min:1|max:12']);
        \App\Models\Periodo::updateOrCreate(['gestion' => $d['gestion'], 'mes' => $d['mes']], ['cerrado' => true, 'user_id' => auth()->id()]);
        return redirect('/contabilidad/cierre')->with('ok', "Mes {$d['mes']}/{$d['gestion']} cerrado y bloqueado.");
    })->middleware('can:contabilidad.asiento');
    Route::get('/contabilidad/csv', function (\App\Application\Lecturas\ContabilidadService $c) {
        $filas = $c->filasDiario();
        return response()->streamDownload(function () use ($filas) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['fecha', 'glosa', 'modulo', 'cuenta', 'debe', 'haber']);
            foreach ($filas as $r) fputcsv($f, $r);
            fclose($f);
        }, 'diario.csv', ['Content-Type' => 'text/csv']);
    })->middleware('can:contabilidad.ver')->name('contabilidad.csv');
    Route::get('/contabilidad/imprimir', fn () => view('print.diario', ['rows' => \App\Models\JournalEntry::with('lines.account')->latest()->limit(200)->get()]))->middleware('can:contabilidad.ver')->name('contabilidad.print');
    Route::get('/facturas', fn () => view('facturas', ['items' => Invoice::latest()->paginate(15)]))->middleware('can:ventas.ver')->name('facturas');
    Route::get('/reportes/{seccion?}', function (\App\Application\Lecturas\ReportesService $rep, string $seccion = 'ventas') {
        abort_unless(in_array($seccion, \App\Application\Lecturas\ReportesService::SECCIONES, true), 404);
        $desde = request('desde', now()->subMonths(11)->startOfMonth()->toDateString());
        $hasta = request('hasta', now()->toDateString());
        $datos = $rep->datos($seccion, $desde, $hasta);
        if (request('csv')) {
            $cab = $rep->columnas($seccion);
            $filas = $rep->filas($seccion, $datos);
            return response()->streamDownload(function () use ($cab, $filas, $seccion) {
                $f = fopen('php://output', 'w');
                fwrite($f, "\xEF\xBB\xBF"); // Excel español abre tildes y columnas
                fputcsv($f, ['reporte', $seccion, now()->toDateString()], ';');
                fputcsv($f, $cab, ';');
                foreach ($filas as $r) fputcsv($f, $r, ';');
                fclose($f);
            }, "reporte-{$seccion}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
        }
        return view('reportes', compact('seccion', 'datos', 'desde', 'hasta'));
    })->middleware('can:reportes.ver')->name('reportes');
    Route::get('/por-cobrar', fn (\App\Application\Lecturas\DeudasService $d) => view('por-cobrar', ['items' => $d->porCobrar(request('buscar'), (bool) request('mora'))]))->middleware('can:ots.ver')->name('por-cobrar');
    Route::get('/por-cobrar/{id}/abonar', fn ($id) => view('form-page', ['comp' => 'abono-form', 'titulo' => 'Abono de cliente', 'id' => $id]))->middleware('can:ots.cerrar');
    Route::get('/por-pagar', fn (\App\Application\Lecturas\DeudasService $d) => view('por-pagar', ['items' => $d->porPagar(request('buscar'))]))->middleware('can:compras.ver')->name('por-pagar');
    Route::get('/por-pagar/{id}/abonar', fn ($id) => view('form-page', ['comp' => 'abono-proveedor-form', 'titulo' => 'Abono a importadora', 'id' => $id]))->middleware('can:compras.crear');
    Route::get('/servicios/{id}/imprimir', function (int $id, \App\Application\Lecturas\OrdenDetalleService $d, \App\Application\Services\SettingService $s) {
        $ot = $d->cargar($id);
        return view('print.orden', ['ot' => $ot, 'empresa' => $s->get('taller.empresa.nombre', 'Taller Alto Tonelaje'), 'diagnosticoBase' => $d->diagnosticoBase($ot->diagnostico), 'grupos' => $d->gruposDe($ot)]);
    })->middleware('can:ots.ver');
    Route::get('/respaldos', [\App\Http\Controllers\RespaldoController::class, 'index']);
    Route::post('/respaldos', [\App\Http\Controllers\RespaldoController::class, 'crear']);
    Route::post('/respaldos/subir-restaurar', [\App\Http\Controllers\RespaldoController::class, 'subirYRestaurar']);
    Route::get('/respaldos/{nombre}/descargar', [\App\Http\Controllers\RespaldoController::class, 'descargar']);
    Route::delete('/respaldos/{nombre}/borrar', [\App\Http\Controllers\RespaldoController::class, 'borrar']);
    Route::post('/respaldos/{nombre}/restaurar', [\App\Http\Controllers\RespaldoController::class, 'restaurar']);
    // Prueba punta a punta de seguimiento de errores: escribe entrada rastreable con ticket E-XXXX.
    Route::get('/logs/demo', function () {
        $ticket = 'E-'.random_int(1000, 9999);
        \Illuminate\Support\Facades\Log::warning("Demo seguimiento errores ticket={$ticket}", ['user' => auth()->id(), 'modulo' => 'demo']);
        return redirect('/log-viewer')->with('ok', "Demo registrada con ticket {$ticket}. Búscalo en el visor.");
    })->name('logs.demo');
    Route::post('/facturas/emitir-demo', function (\App\Application\UseCases\EmitirFacturaUseCase $uc) {
        $cli = \App\Models\Client::first();
        abort_if(! $cli, 422, 'Cree un cliente primero.');
        $inv = $uc->ejecutar('servicio', 1, $cli->id, 100.00);
        return redirect('/facturas')->with('ok', 'Factura '.$inv->cuf.' emitida (mock).');
    })->middleware('can:facturas.emitir');
});
