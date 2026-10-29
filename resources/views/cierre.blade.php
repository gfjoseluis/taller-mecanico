@extends('layouts.admin')
@section('content')
<h1 class="text-xl font-bold mb-3">Cierre de mes</h1>
@if(session('ok'))<p class="bg-green-100 p-2 rounded mb-2">{{ session('ok') }}</p>@endif
<div class="bg-white p-4 rounded shadow w-full">
<p class="text-sm mb-2">El mes cerrado se bloquea: nadie lo edita (los errores se corrigen con un registro nuevo). Solo encargado.</p>
<form method="POST" action="/contabilidad/cierre">@csrf<div class="grid grid-cols-3 gap-2"><input type="number" name="gestion" value="{{ now()->year }}" class="border p-2 rounded" aria-label="Año"><input type="number" name="mes" min="1" max="12" value="{{ now()->month }}" class="border p-2 rounded" aria-label="Mes"><button class="bg-slate-900 text-white px-4 py-2 rounded">Cerrar mes</button></div></form>
<h2 class="font-bold mt-4 mb-2">Meses</h2>
<table class="w-full text-sm"><thead><tr class="text-left border-b"><th>Año</th><th>Mes</th><th>Estado</th></tr></thead><tbody>@foreach($periodos as $p)<tr class="border-b"><td>{{ $p->gestion }}</td><td>{{ $p->mes }}</td><td>{{ $p->cerrado ? '🔒 Cerrado' : 'Abierto' }}</td></tr>@endforeach</tbody></table>
</div>
@endsection
