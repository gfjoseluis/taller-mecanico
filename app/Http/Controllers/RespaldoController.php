<?php

namespace App\Http\Controllers;

use App\Application\Services\RespaldoService;
use Illuminate\Http\Request;

class RespaldoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:config.editar');
    }

    public function index(RespaldoService $r)
    {
        return view('respaldos', ['items' => $r->lista()]);
    }

    public function crear(RespaldoService $r)
    {
        $nombre = $r->crear();
        return redirect('/respaldos')->with('ok', "Respaldo {$nombre} creado y guardado.");
    }

    public function descargar(RespaldoService $r, string $nombre)
    {
        return response()->download($r->ruta($nombre));
    }

    public function borrar(RespaldoService $r, string $nombre)
    {
        abort_unless(auth()->user()->can('admin.purge'), 403);
        unlink($r->ruta($nombre));
        return redirect('/respaldos')->with('ok', 'Respaldo borrado.');
    }

    public function restaurar(Request $req, RespaldoService $r, string $nombre)
    {
        abort_unless(auth()->user()->can('admin.purge'), 403);
        $req->validate(['confirmacion' => 'required|in:RESTAURAR', 'password' => 'required']);
        $r->restaurar($nombre, $req->password);
        return redirect('/respaldos')->with('ok', 'Restaurado. El sistema estuvo 1 minuto en mantenimiento.');
    }
}
